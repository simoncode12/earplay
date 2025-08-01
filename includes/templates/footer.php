</main>
    <footer>
        </footer>

    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const notifIcon = document.getElementById('notification-icon');
        const notifPanel = document.getElementById('notification-panel');
        const notifBadge = document.getElementById('notification-badge');
        const notifList = document.getElementById('notification-list');
        const markAllReadBtn = document.getElementById('mark-all-read-btn');

        const fetchNotifications = async () => {
            try {
                const response = await fetch('/api/notifications_api.php');
                const data = await response.json();

                // Update badge
                if (data.unread_count > 0) {
                    notifBadge.textContent = data.unread_count;
                    notifBadge.style.display = 'flex';
                } else {
                    notifBadge.style.display = 'none';
                }

                // Update daftar notifikasi
                notifList.innerHTML = '';
                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notif => {
                        const item = document.createElement('div');
                        item.className = `notification-item ${!notif.is_read ? 'unread' : ''}`;
                        item.innerHTML = `<p>${notif.message}</p><small>${notif.created_at}</small>`;
                        notifList.appendChild(item);
                    });
                } else {
                    notifList.innerHTML = '<p class="no-notif">Tidak ada notifikasi baru.</p>';
                }
            } catch (error) {
                console.error("Gagal mengambil notifikasi:", error);
            }
        };

        if (notifIcon) {
            notifIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                notifPanel.style.display = notifPanel.style.display === 'block' ? 'none' : 'block';
                if (notifPanel.style.display === 'block') {
                    fetchNotifications();
                }
            });
        }

        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                await fetch('/api/notifications_api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=mark_all_read'
                });
                fetchNotifications(); // Refresh daftar setelah menandai
            });
        }

        document.addEventListener('click', (e) => {
            if (notifPanel && !notifPanel.contains(e.target) && !notifIcon.contains(e.target)) {
                notifPanel.style.display = 'none';
            }
        });

        // Cek notifikasi setiap 30 detik
        setInterval(fetchNotifications, 30000);
        // Panggil sekali saat halaman dimuat
        fetchNotifications();
    });
    </script>
    <?php endif; ?>
</body>
</html>