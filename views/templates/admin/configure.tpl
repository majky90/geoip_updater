<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="material-icons align-middle">download</i>
            <span class="h5 ml-2">{$module->l('GeoLite2 Database Updater')|escape:'html':'UTF-8'}</span>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>{$module->l('Recommended schedule:')|escape:'html':'UTF-8'}</strong>
            {$module->l('Schedule: Every Tuesday and Friday at 06:00 UTC. Set your cron job to run after this time, not at the exact moment the GeoIP file is generated.')|escape:'html':'UTF-8'}
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-secondary mb-3">
                    <div class="card-header">{$module->l('Database status')|escape:'html':'UTF-8'}</div>
                    <div class="card-body">
                        <p>
                            {if $file_exists}
                                <span class="badge badge-success">{$module->l('Exists')|escape:'html':'UTF-8'}</span>
                            {else}
                                <span class="badge badge-danger">{$module->l('Missing')|escape:'html':'UTF-8'}</span>
                            {/if}
                        </p>
                        <p><strong>{$module->l('Last update:')|escape:'html':'UTF-8'}</strong> {$last_update|escape:'html':'UTF-8'}</p>
                        <p><strong>{$module->l('Database path:')|escape:'html':'UTF-8'}</strong> {$mmdb_path|escape:'html':'UTF-8'}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-secondary mb-3">
                    <div class="card-header">{$module->l('Cron configuration')|escape:'html':'UTF-8'}</div>
                    <div class="card-body">
                        <p>{$module->l('Use the full URL below in your cron job. Schedule it to run after the database generation time, not exactly at 06:00 UTC.')|escape:'html':'UTF-8'}</p>
                        <div class="input-group mb-3">
                            <textarea id="geoip-cron-url" class="form-control" rows="2" readonly>{$cron_url|escape:'html':'UTF-8'}</textarea>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="copy-cron-url">
                                    {$module->l('Copy URL')|escape:'html':'UTF-8'}
                                </button>
                            </div>
                        </div>
                        <p class="small text-muted">{$module->l('Schedule: Every Tuesday and Friday at 06:00 UTC - cron expression: 0 6 * * 2,5')|escape:'html':'UTF-8'}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <form method="post">
                    <button type="submit" name="submit_manual_update" class="btn btn-primary">
                        {$module->l('Manual Update')|escape:'html':'UTF-8'}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        var copyButton = document.getElementById('copy-cron-url');
        var cronInput = document.getElementById('geoip-cron-url');

        if (copyButton && cronInput) {
            copyButton.addEventListener('click', function () {
                cronInput.select();
                cronInput.setSelectionRange(0, 99999);
                document.execCommand('copy');
                var originalText = copyButton.textContent;
                copyButton.textContent = '{$module->l('Copied!')|escape:'html':'UTF-8'}';
                setTimeout(function () {
                    copyButton.textContent = originalText;
                }, 1500);
            });
        }
    });
</script>
