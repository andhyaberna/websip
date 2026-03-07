<?php ob_start(); ?>

<!-- Breadcrumb -->
<nav class="flex mb-6" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-3">
    <li class="inline-flex items-center">
      <a href="<?php echo base_url('admin/dashboard'); ?>" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
        Dashboard
      </a>
    </li>
    <li>
      <div class="flex items-center">
        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Settings</span>
      </div>
    </li>
  </ol>
</nav>

<h1 class="text-2xl font-bold text-gray-800 mb-6">Configuration</h1>

<!-- Notifications -->
<?php if (isset($_GET['success'])): ?>
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
  <p class="font-bold">Success</p>
  <p><?php echo htmlspecialchars($_GET['success']); ?></p>
</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
  <p class="font-bold">Error</p>
  <p><?php echo htmlspecialchars($_GET['error']); ?></p>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="mb-4 border-b border-gray-200">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="settingsTab" data-tabs-toggle="#settingsTabContent" role="tablist">
        <li class="mr-2" role="presentation">
            <button class="inline-block p-4 rounded-t-lg border-b-2 text-indigo-600 border-indigo-600 hover:text-indigo-600 hover:border-indigo-600" id="starsender-tab" data-tabs-target="#starsender" type="button" role="tab" aria-controls="starsender" aria-selected="true">Starsender</button>
        </li>
        <li class="mr-2" role="presentation">
            <button class="inline-block p-4 rounded-t-lg border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="mailketing-tab" data-tabs-target="#mailketing" type="button" role="tab" aria-controls="mailketing" aria-selected="false">Mailketing</button>
        </li>
        <li class="mr-2" role="presentation">
            <button class="inline-block p-4 rounded-t-lg border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="backup-tab" data-tabs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="false">Backup & Restore</button>
        </li>
    </ul>
</div>

<div id="settingsTabContent">
    <!-- Starsender Tab -->
    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="starsender" role="tabpanel" aria-labelledby="starsender-tab">
        <form action="<?php echo base_url('admin/settings/update'); ?>" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
            
            <div class="grid grid-cols-1 gap-6">
                <div class="flex items-center mb-4">
                    <input id="starsender_enabled" name="starsender_enabled" type="checkbox" value="1" <?php echo Settings::get('starsender_enabled') ? 'checked' : ''; ?> class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="starsender_enabled" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Enable Starsender Integration</label>
                </div>

                <div>
                    <label for="starsender_api_key" class="block mb-2 text-sm font-medium text-gray-900">API Key</label>
                    <input type="password" id="starsender_api_key" name="starsender_api_key" value="<?php echo htmlspecialchars($settings['starsender_api_key'] ?? ''); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" placeholder="Enter Starsender API Key">
                    <p class="mt-1 text-xs text-gray-500">Your secret API key from Starsender dashboard.</p>
                </div>
                <div>
                    <label for="starsender_endpoint" class="block mb-2 text-sm font-medium text-gray-900">API Endpoint</label>
                    <input type="text" id="starsender_endpoint" name="starsender_endpoint" value="<?php echo htmlspecialchars($settings['starsender_endpoint'] ?? 'https://starsender.online/api/sendText'); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="starsender_timeout" class="block mb-2 text-sm font-medium text-gray-900">Timeout (seconds)</label>
                        <input type="number" id="starsender_timeout" name="starsender_timeout" value="<?php echo htmlspecialchars($settings['starsender_timeout'] ?? '30'); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                    </div>
                    <div>
                        <label for="starsender_retry" class="block mb-2 text-sm font-medium text-gray-900">Retry Attempts</label>
                        <input type="number" id="starsender_retry" name="starsender_retry" value="<?php echo htmlspecialchars($settings['starsender_retry'] ?? '3'); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                    </div>
                </div>
                <div>
                    <label for="starsender_template_welcome" class="block mb-2 text-sm font-medium text-gray-900">Welcome Message Template</label>
                    <textarea id="starsender_template_welcome" name="starsender_template_welcome" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($settings['starsender_template_welcome'] ?? ''); ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">Available placeholders: {name}, {email}</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t">
                <button type="submit" class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save Starsender Settings</button>
                
                <div class="flex items-center gap-2">
                    <input type="text" id="test_wa_target" placeholder="628xxx (Target Number)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 w-48">
                    <button type="button" onclick="testConnection('starsender')" class="text-indigo-700 bg-white border border-indigo-700 hover:bg-indigo-50 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Test Connection</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Mailketing Tab -->
    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="mailketing" role="tabpanel" aria-labelledby="mailketing-tab">
        <form action="<?php echo base_url('admin/settings/update'); ?>" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
            
            <div class="flex items-center mb-4">
                <input id="mailketing_enabled" name="mailketing_enabled" type="checkbox" value="1" <?php echo Settings::get('mailketing_enabled') ? 'checked' : ''; ?> class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <label for="mailketing_enabled" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Enable Mailketing Integration</label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label for="mailketing_api_token" class="block mb-2 text-sm font-medium text-gray-900">API Token</label>
                    <input type="password" id="mailketing_api_token" name="mailketing_api_token" value="<?php echo htmlspecialchars($settings['mailketing_api_token'] ?? ''); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>
                
                <div>
                    <label for="mailketing_smtp_host" class="block mb-2 text-sm font-medium text-gray-900">SMTP Host</label>
                    <input type="text" id="mailketing_smtp_host" name="mailketing_smtp_host" value="<?php echo htmlspecialchars($settings['mailketing_smtp_host'] ?? 'smtp.mailketing.co.id'); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="mailketing_smtp_port" class="block mb-2 text-sm font-medium text-gray-900">SMTP Port</label>
                    <input type="number" id="mailketing_smtp_port" name="mailketing_smtp_port" value="<?php echo htmlspecialchars($settings['mailketing_smtp_port'] ?? '587'); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>
                
                <div>
                    <label for="mailketing_smtp_user" class="block mb-2 text-sm font-medium text-gray-900">SMTP User</label>
                    <input type="text" id="mailketing_smtp_user" name="mailketing_smtp_user" value="<?php echo htmlspecialchars($settings['mailketing_smtp_user'] ?? ''); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="mailketing_smtp_pass" class="block mb-2 text-sm font-medium text-gray-900">SMTP Password</label>
                    <input type="password" id="mailketing_smtp_pass" name="mailketing_smtp_pass" value="<?php echo htmlspecialchars($settings['mailketing_smtp_pass'] ?? ''); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>

                <div>
                    <label for="mailketing_sender_email" class="block mb-2 text-sm font-medium text-gray-900">Sender Email</label>
                    <input type="email" id="mailketing_sender_email" name="mailketing_sender_email" value="<?php echo htmlspecialchars($settings['mailketing_sender_email'] ?? ''); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>
                <div>
                    <label for="mailketing_sender_name" class="block mb-2 text-sm font-medium text-gray-900">Sender Name</label>
                    <input type="text" id="mailketing_sender_name" name="mailketing_sender_name" value="<?php echo htmlspecialchars($settings['mailketing_sender_name'] ?? ''); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t">
                <button type="submit" class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save Mailketing Settings</button>
                <button type="button" onclick="testConnection('mailketing')" class="text-indigo-700 bg-white border border-indigo-700 hover:bg-indigo-50 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Test Connection</button>
            </div>
        </form>
    </div>

    <!-- Backup & Restore Tab -->
    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="backup" role="tabpanel" aria-labelledby="backup-tab">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Export -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Configuration</h3>
                <p class="text-sm text-gray-500 mb-6">Download a backup of all current settings in JSON format. Keep this file secure as it contains API keys and passwords.</p>
                <a href="<?php echo base_url('admin/settings/export'); ?>" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Backup (.json)
                </a>
            </div>

            <!-- Import -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Restore Configuration</h3>
                <p class="text-sm text-gray-500 mb-6">Restore settings from a previously exported JSON file. Existing settings will be overwritten.</p>
                <form action="<?php echo base_url('admin/settings/import'); ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900" for="config_file">Upload JSON File</label>
                        <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" id="config_file" name="config_file" type="file" accept=".json" required>
                    </div>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Restore Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Test Result Modal -->
<div id="testResultModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative bg-white rounded-lg shadow">
            <div class="flex justify-between items-start p-4 rounded-t border-b">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Test Result</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" onclick="document.getElementById('testResultModal').classList.add('hidden')">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <p class="text-base leading-relaxed text-gray-500" id="modalMessage">Testing connection...</p>
                <pre class="bg-gray-100 p-2 rounded text-xs hidden overflow-auto max-h-40" id="modalRaw"></pre>
            </div>
            <div class="flex items-center p-6 space-x-2 rounded-b border-t border-gray-200">
                <button type="button" onclick="document.getElementById('testResultModal').classList.add('hidden')" class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Simple Tab Implementation
document.querySelectorAll('[role="tab"]').forEach(tab => {
    tab.addEventListener('click', (e) => {
        e.preventDefault();
        
        // Deactivate all
        document.querySelectorAll('[role="tab"]').forEach(t => {
            t.classList.remove('text-indigo-600', 'border-indigo-600');
            t.classList.add('border-transparent', 'hover:text-gray-600');
            t.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('[role="tabpanel"]').forEach(p => {
            p.classList.add('hidden');
        });
        
        // Activate current
        tab.classList.remove('border-transparent', 'hover:text-gray-600');
        tab.classList.add('text-indigo-600', 'border-indigo-600');
        tab.setAttribute('aria-selected', 'true');
        
        const target = tab.getAttribute('data-tabs-target');
        document.querySelector(target).classList.remove('hidden');
    });
});

function testConnection(service) {
    const modal = document.getElementById('testResultModal');
    const title = document.getElementById('modalTitle');
    const message = document.getElementById('modalMessage');
    const raw = document.getElementById('modalRaw');
    
    title.innerText = 'Testing ' + service + '...';
    message.innerText = 'Please wait...';
    raw.classList.add('hidden');
    modal.classList.remove('hidden');
    
    const formData = new FormData();
    formData.append('service', service);
    
    if (service === 'starsender') {
        formData.append('api_key', document.getElementById('starsender_api_key').value);
        formData.append('endpoint', document.getElementById('starsender_endpoint').value);
        formData.append('test_target', document.getElementById('test_wa_target').value);
    } else if (service === 'mailketing') {
        formData.append('smtp_host', document.getElementById('mailketing_smtp_host').value);
        formData.append('smtp_port', document.getElementById('mailketing_smtp_port').value);
        formData.append('smtp_user', document.getElementById('mailketing_smtp_user').value);
        formData.append('smtp_pass', document.getElementById('mailketing_smtp_pass').value);
    }
    
    fetch('<?php echo base_url('admin/settings/test'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        title.innerText = data.status === 'success' ? 'Connection Successful' : 'Connection Failed';
        message.innerText = data.message;
        message.className = data.status === 'success' ? 'text-green-600 font-medium' : 'text-red-600 font-medium';
        
        if (data.response) {
            raw.innerText = data.response;
            raw.classList.remove('hidden');
        }
    })
    .catch(error => {
        title.innerText = 'Error';
        message.innerText = 'An unexpected error occurred.';
        console.error(error);
    });
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/admin.php'; ?>
