<!doctype html>
<html lang="en">

<head>
    <?php if ($instance->settings->get('analytics.google_tag')): ?>
        <?= $instance->settings->get('analytics.google_tag'); ?>
    <?php endif; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta property="og:type" content="website">

    <?php if ($instance->meta->title): ?>
        <title>
            <?= $instance->meta->title; ?>     <?php if ($instance->meta->extraTitle): ?> -
                <?= $instance->meta->extraTitle; ?>     <?php endif; ?>
        </title>
        <meta property="og:title"
            content="<?= $instance->meta->title; ?><?php if ($instance->meta->extraTitle): ?> - <?= $instance->meta->extraTitle; ?><?php endif; ?>">
        <meta name="twitter:title"
            content="<?= $instance->meta->title; ?><?php if ($instance->meta->extraTitle): ?> - <?= $instance->meta->extraTitle; ?><?php endif; ?>">
    <?php endif; ?>

    <?php if ($instance->meta->description): ?>
        <meta name="description" content="<?= $instance->meta->description; ?>">
        <meta property="og:description" content="<?= $instance->meta->description; ?>">
        <meta name="twitter:description" content="<?= $instance->meta->description; ?>">
    <?php endif; ?>

    <?php if ($instance->meta->keywords): ?>
        <meta name="keywords" content="<?= $instance->meta->keywords; ?>">
    <?php endif; ?>

    <?php if ($instance->meta->url): ?>
        <meta property="og:url" content="<?= $instance->meta->url; ?>">
    <?php endif; ?>

    <?php if ($instance->meta->image): ?>
        <meta property="og:image" content="<?= $instance->meta->image; ?>">
        <meta name="twitter:image" content="<?= $instance->meta->image; ?>">
    <?php endif; ?>

    <?php foreach (APPLICATION_STYLESHEETS as $stylesheet): ?>
        <link rel="stylesheet"
            href="<?= $stylesheet . '?v=' . ($_ENV['CURRENT_ENV'] === 'dev' || $_ENV['CURRENT_ENV'] === 'test' ? time() : APPLICATION_VERSION); ?>" />
    <?php endforeach; ?>

    <script>
        window.APP = {
            BASE_URL: "<?= $instance->baseUrl; ?>",
            ACTIVE_SESSION: <?= $instance->auth->user ? 'true' : 'false'; ?>,
            REFERER: '<?= $request->referer() ? $request->referer() : false; ?>'
        };
    </script>

    <?php foreach (APPLICATION_HEADER_SCRIPTS as $script): ?>
        <script
            src="<?= $script . '?v=' . ($_ENV['CURRENT_ENV'] === 'dev' || $_ENV['CURRENT_ENV'] === 'test' ? time() : APPLICATION_VERSION); ?>">
            </script>
    <?php endforeach; ?>
</head>

<body>
