<?php ob_start(); ?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Pengaturan Akun</h1>

    <!-- Tabs Header -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="profileTabs" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 active-tab" id="general-tab" data-tabs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">Profil Umum</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="security-tab" data-tabs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">Keamanan</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="preferences-tab" data-tabs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">Preferensi & Privasi</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="activity-tab" data-tabs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">Aktivitas</button>
            </li>
        </ul>
    </div>

    <div id="profileTabContent">
        <!-- General Tab -->
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="general" role="tabpanel" aria-labelledby="general-tab">
            <form action="<?php echo base_url('profile/update'); ?>" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-2xl">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="flex items-center space-x-6">
                    <div class="shrink-0">
                        <?php if (!empty($user['avatar'])): ?>
                            <img class="h-24 w-24 object-cover rounded-full" src="<?php echo base_url($user['avatar']); ?>" alt="Current profile photo" />
                        <?php else: ?>
                            <div class="h-24 w-24 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 text-2xl font-bold">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label class="block">
                        <span class="sr-only">Choose profile photo</span>
                        <input type="file" name="avatar" class="block w-full text-sm text-slate-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-full file:border-0
                          file:text-sm file:font-semibold
                          file:bg-indigo-50 file:text-indigo-700
                          hover:file:bg-indigo-100
                        "/>
                    </label>
                </div>

                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                </div>

                <div>
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">Nomor Telepon (WhatsApp)</label>
                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>

                <button type="submit" class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Simpan Perubahan</button>
            </form>
        </div>

        <!-- Security Tab -->
        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="security" role="tabpanel" aria-labelledby="security-tab">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Change Password -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ganti Password</h3>
                    <form action="<?php echo base_url('profile/password'); ?>" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div>
                            <label for="current_password" class="block mb-2 text-sm font-medium text-gray-900">Password Saat Ini</label>
                            <input type="password" name="current_password" id="current_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="new_password" class="block mb-2 text-sm font-medium text-gray-900">Password Baru</label>
                            <input type="password" name="new_password" id="new_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                            <p class="mt-1 text-xs text-gray-500">Min 8 karakter, huruf besar, kecil, angka, simbol.</p>
                        </div>
                        <div>
                            <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-900">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                        </div>
                        <button type="submit" class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Ubah Password</button>
                    </form>
                </div>

                <!-- Change Email & 2FA -->
                <div class="space-y-8">
                    <!-- Email -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Ganti Email</h3>
                        <p class="text-sm text-gray-600 mb-2">Email saat ini: <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
                        
                        <?php if (!empty($preferences['pending_email_change'])): ?>
                            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-4">
                                <p class="text-sm text-yellow-800 mb-2">Verifikasi Email Baru: <strong><?php echo htmlspecialchars($preferences['pending_email_change']); ?></strong></p>
                                <form action="<?php echo base_url('profile/verify-email'); ?>" method="POST" class="flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="text" name="otp_code" placeholder="Kode OTP" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-32 p-2.5" required>
                                    <button type="submit" class="text-white bg-indigo-700 hover:bg-indigo-800 font-medium rounded-lg text-sm px-4 py-2.5">Verifikasi</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo base_url('profile/email'); ?>" method="POST" class="flex gap-2 items-end">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="flex-1">
                                <label for="new_email" class="block mb-2 text-sm font-medium text-gray-900">Email Baru</label>
                                <input type="email" name="new_email" id="new_email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                            </div>
                            <button type="submit" class="text-indigo-700 bg-white border border-indigo-700 hover:bg-indigo-50 font-medium rounded-lg text-sm px-5 py-2.5">Kirim OTP</button>
                        </form>
                    </div>

                    <!-- 2FA Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Two-Factor Authentication (2FA)</h3>
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Google Authenticator</h4>
                                    <p class="text-xs text-gray-500">Amankan akun Anda dengan kode 2FA.</p>
                                </div>
                                <?php if ($user['two_factor_enabled']): ?>
                                    <form action="<?php echo base_url('profile/2fa/disable'); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan 2FA?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-4 py-2">Nonaktifkan</button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" id="setup2FABtn" class="text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-4 py-2">Aktifkan 2FA</button>
                                <?php endif; ?>
                            </div>

                            <?php if (!$user['two_factor_enabled']): ?>
                                <div id="setup2FAContainer" class="hidden border-t border-gray-200 pt-4 mt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="text-center">
                                            <p class="text-sm text-gray-600 mb-2">Scan QR Code ini dengan aplikasi Authenticator:</p>
                                            <img id="qrImage" src="" alt="QR Code" class="mx-auto border p-2 bg-white rounded-lg">
                                            <p class="text-xs text-gray-500 mt-2">Atau masukkan kode manual: <strong id="secretCode" class="font-mono"></strong></p>
                                        </div>
                                        <div>
                                            <form action="<?php echo base_url('profile/2fa/confirm'); ?>" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <label for="2fa_code" class="block mb-2 text-sm font-medium text-gray-900">Masukkan Kode Verifikasi</label>
                                                <input type="text" name="code" id="2fa_code" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 mb-4" placeholder="Contoh: 123456" required>
                                                <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Verifikasi & Aktifkan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferences Tab -->
        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
            <form action="<?php echo base_url('profile/preferences'); ?>" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Notifikasi</h3>
                    <p class="text-sm text-gray-500 mb-4">Pilih channel notifikasi yang Anda inginkan.</p>
                    
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Jenis Notifikasi</th>
                                    <th scope="col" class="py-3 px-6 text-center">Email</th>
                                    <th scope="col" class="py-3 px-6 text-center">WhatsApp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">
                                        Login Alert
                                        <p class="text-xs font-normal text-gray-500">Info saat ada login baru di akun Anda</p>
                                    </th>
                                    <td class="py-4 px-6 text-center">
                                        <input id="notify_login_email" name="notify_login_email" type="checkbox" value="1" <?php echo ($preferences['notify_login_email'] ?? '1') == '1' ? 'checked' : ''; ?> class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <input id="notify_login_wa" name="notify_login_wa" type="checkbox" value="1" <?php echo ($preferences['notify_login_wa'] ?? '1') == '1' ? 'checked' : ''; ?> class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                </tr>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">
                                        Promo & Update
                                        <p class="text-xs font-normal text-gray-500">Info produk baru dan penawaran spesial</p>
                                    </th>
                                    <td class="py-4 px-6 text-center">
                                        <input id="notify_promo_email" name="notify_promo_email" type="checkbox" value="1" <?php echo ($preferences['notify_promo_email'] ?? '1') == '1' ? 'checked' : ''; ?> class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <input id="notify_promo_wa" name="notify_promo_wa" type="checkbox" value="1" <?php echo ($preferences['notify_promo_wa'] ?? '1') == '1' ? 'checked' : ''; ?> class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                </tr>
                                <tr class="bg-white hover:bg-gray-50">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">
                                        Transaksi
                                        <p class="text-xs font-normal text-gray-500">Status pembayaran dan pesanan</p>
                                    </th>
                                    <td class="py-4 px-6 text-center">
                                        <input id="notify_transaction_email" name="notify_transaction_email" type="checkbox" value="1" <?php echo ($preferences['notify_transaction_email'] ?? '1') == '1' ? 'checked' : ''; ?> class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <input id="notify_transaction_wa" name="notify_transaction_wa" type="checkbox" value="1" <?php echo ($preferences['notify_transaction_wa'] ?? '1') == '1' ? 'checked' : ''; ?> class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <button type="submit" class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Simpan Preferensi</button>
            </form>
        </div>

        <!-- Activity Tab -->
        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="activity" role="tabpanel" aria-labelledby="activity-tab">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Login Terakhir</h3>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Waktu</th>
                            <th scope="col" class="px-6 py-3">IP Address</th>
                            <th scope="col" class="px-6 py-3">User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($loginHistory)): ?>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td colspan="3" class="px-6 py-4 text-center">Belum ada data login.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($loginHistory as $log): ?>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4"><?php echo $log['created_at']; ?></td>
                                    <td class="px-6 py-4"><?php echo $log['ip_address']; ?></td>
                                    <td class="px-6 py-4 truncate max-w-xs" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                        <?php echo htmlspecialchars($log['user_agent']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple Tab Implementation
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('[role="tab"]');
        const tabContents = document.querySelectorAll('[role="tabpanel"]');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetId = tab.getAttribute('data-tabs-target').substring(1); // remove #
                
                // Hide all contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('active-tab', 'border-indigo-600', 'text-indigo-600');
                    t.classList.add('border-transparent', 'hover:text-gray-600');
                    t.setAttribute('aria-selected', 'false');
                });

                // Show target content
                document.getElementById(targetId).classList.remove('hidden');
                
                // Add active class to clicked tab
                tab.classList.add('active-tab', 'border-indigo-600', 'text-indigo-600');
                tab.classList.remove('border-transparent', 'hover:text-gray-600');
                tab.setAttribute('aria-selected', 'true');
            });
        });

        // Initialize first tab or hash tab
        if(window.location.hash) {
            const hashTab = document.querySelector(`[data-tabs-target="${window.location.hash}"]`);
            if(hashTab) hashTab.click();
        } else {
            tabs[0].click();
        }
    });

    // 2FA Setup Script
    const setupBtn = document.getElementById('setup2FABtn');
    if(setupBtn) {
        setupBtn.addEventListener('click', async () => {
            setupBtn.disabled = true;
            setupBtn.innerText = 'Loading...';
            
            try {
                const response = await fetch('<?php echo base_url('profile/2fa/setup'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'csrf_token=<?php echo $csrf_token; ?>'
                });
                const data = await response.json();
                
                if(data.error) {
                    alert(data.error);
                    return;
                }
                
                document.getElementById('qrImage').src = data.qr_url;
                document.getElementById('secretCode').innerText = data.secret;
                document.getElementById('setup2FAContainer').classList.remove('hidden');
                setupBtn.classList.add('hidden');
            } catch (e) {
                alert('Gagal memuat data 2FA');
            } finally {
                setupBtn.disabled = false;
                setupBtn.innerText = 'Aktifkan 2FA';
            }
        });
    }
</script>

<style>
    .active-tab {
        border-color: #4f46e5 !important;
        color: #4f46e5 !important;
    }
</style>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/' . $layout . '.php'; ?>
