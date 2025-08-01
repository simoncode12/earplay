/**
 * TubeX Reward & Player Handler
 * @version 7.2 (Membaca Harga Iklan dari VAST Extension)
 */
async function initTubeXPlayer(data) {
    const { video_id, youtube_id, user_id, reward_rate, vast_tag, ad_campaign_id } = data;

    // --- Konfigurasi Dasar Player ---
    const videoUrl = `https://inv-eu3.nadeko.net/api/manifest/dash/id/${youtube_id}?local=true`;
    const posterUrl = `https://i.ytimg.com/vi/${youtube_id}/hqdefault.jpg`;

    const playerConfig = {
        sources: [{ file: videoUrl, type: "application/dash+xml" }],
        image: posterUrl,
        width: "100%",
        height: "100%",
        autostart: true
    };

    // --- Logika Iklan Dinamis & Konfigurasi Baru ---
    if (vast_tag && vast_tag.length > 10) {
        playerConfig.advertising = {
            client: "vast",
            withCredentials: true,
            adscheduleid: `tubex_${video_id}_${new Date().getTime()}`,
            schedule: {
                adbreak: {
                    tag: vast_tag,
                    offset: "pre"
                }
            }
        };
        console.log("VAST Ad Tag dimuat:", vast_tag);
    } else {
        console.log("Tidak ada VAST Ad Tag, iklan tidak dimuat.");
    }

    const playerInstance = jwplayer("plays");
    playerInstance.setup(playerConfig);

    // --- Event Listener untuk Pelacakan Pendapatan Iklan (RTB) ---
    playerInstance.on('adImpression', (event) => {
        let adRevenue = 0;
        try {
            // JW Player akan mengurai blok <Extension> yang kita buat di proxy
            const extensions = event.adVastAd?.extensions || [];
            const revenueExtension = extensions.find(ext => ext.type === 'TubeX-Revenue');
            if (revenueExtension) {
                // Ambil nilai harga dari node <Revenue>
                adRevenue = parseFloat(revenueExtension.Revenue.nodeValue);
            }
        } catch (e) {
            console.error("Gagal membaca revenue dari VAST Extension:", e);
        }

        console.log(`Ad Impression: Pendapatan terdeteksi $${adRevenue}`);

        // Kirim laporan ke server HANYA jika ada pendapatan
        if (adRevenue > 0) {
            fetch('/api/track_ad.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    video_id: video_id,
                    campaign_id: ad_campaign_id,
                    revenue: adRevenue
                })
            });
        }
    });

    playerInstance.on('adError', (event) => {
        console.error("AD ERROR:", event.message);
    });

    // --- Logika Pelacakan & Reward ---
    const isUserLoggedIn = !!user_id;

    const secondsEl = isUserLoggedIn ? document.getElementById('seconds') : null;
    const usdEl = isUserLoggedIn ? document.getElementById('usd') : null;
    const statusEl = isUserLoggedIn ? document.getElementById('status') : null;

    async function fetchInitialProgress() {
        if (!isUserLoggedIn) return 0;
        try {
            const response = await fetch(`/api/update_watch_time.php?video_id=${video_id}`);
            if (!response.ok) return 0;
            const data = await response.json();
            return data.watched_seconds || 0;
        } catch (error) {
            console.error("Gagal mengambil progres awal:", error);
            return 0;
        }
    }

    const initialSeconds = await fetchInitialProgress();
    let totalValidSeconds = initialSeconds;
    let lastReportedTime = initialSeconds;
    let lastPlayerPosition = initialSeconds;

    if (isUserLoggedIn && statusEl) {
        statusEl.textContent = "▶️ Memuat Video...";
    }

    playerInstance.on('ready', function() {
        if (initialSeconds > 0) {
            playerInstance.seek(initialSeconds);
        }
    });

    const sendProgressToServer = async (secondsToReport) => {
        if (secondsToReport <= lastReportedTime) return;
        try {
            const formData = new FormData();
            formData.append('video_id', video_id);
            formData.append('watched_seconds', secondsToReport);
            const response = await fetch('/api/update_watch_time.php', { method: 'POST', body: formData });
            if (response.ok) {
                lastReportedTime = secondsToReport;
                console.log(`Progres ${secondsToReport} detik terkirim ke server.`);
            }
        } catch (error) {
            console.error("Gagal mengirim progres:", error);
            if (isUserLoggedIn && statusEl) statusEl.textContent = "❌ Gagal terhubung.";
        }
    };

    const rewardInterval = setInterval(() => {
        const playerPosition = Math.floor(playerInstance.getPosition());
        const playerState = playerInstance.getState();

        if (playerState === "playing" && playerPosition > lastPlayerPosition) {
            const elapsedSeconds = playerPosition - lastPlayerPosition;
            totalValidSeconds += elapsedSeconds;
            lastPlayerPosition = playerPosition;

            if (isUserLoggedIn && statusEl) {
                const totalUSD = totalValidSeconds * reward_rate;
                statusEl.textContent = "✅ Reward aktif";
                secondsEl.textContent = totalValidSeconds;
                usdEl.textContent = '$' + totalUSD.toFixed(8);
            }

            if (totalValidSeconds - lastReportedTime >= 10) {
                 sendProgressToServer(totalValidSeconds);
            }
        } else if (playerState === "paused" && isUserLoggedIn && statusEl) {
            statusEl.textContent = "⏸️ Video dijeda";
        } else if (playerState === "buffering" && isUserLoggedIn && statusEl) {
            statusEl.textContent = "⏳ Memuat...";
        } else if ((playerState === "idle" || playerState === "complete")) {
            sendProgressToServer(totalValidSeconds);
            clearInterval(rewardInterval);
        }
    }, 1000);

    window.addEventListener('beforeunload', () => {
        if (totalValidSeconds > lastReportedTime) {
            sendProgressToServer(totalValidSeconds);
        }
    });
}