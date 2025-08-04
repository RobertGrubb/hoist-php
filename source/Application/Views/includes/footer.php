<?php foreach (APPLICATION_SCRIPTS as $script): ?>
    <script
        src="<?= $script . '?v=' . ($_ENV['CURRENT_ENV'] === 'dev' || $_ENV['CURRENT_ENV'] === 'test' ? time() : APPLICATION_VERSION); ?>">
        </script>
<?php endforeach; ?>

<?php foreach (APPLICATION_MODULE_SCRIPTS as $script): ?>
    <script type="module"
        src="<?= $script . '?v=' . ($_ENV['CURRENT_ENV'] === 'dev' || $_ENV['CURRENT_ENV'] === 'test' ? time() : APPLICATION_VERSION); ?>">
        </script>
<?php endforeach; ?>

</body>
