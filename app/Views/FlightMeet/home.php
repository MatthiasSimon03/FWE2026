<?= $this->extend('FlightMeet/layout') ?>

<?= $this->section('content') ?>
<h1>FlightMeet <img src="<?= base_url('assets/icons/gliderIcon.png') ?>" alt="Icon" class="fm-icon"></h1>
<p class="lead">
    FlightMeet ist eine Plattform, auf der Fluginteressierte sich zu
    Flugtreffen verabreden und Gruppen bilden können.
</p>

<div class="actions">
    <a class="btn" href="<?= base_url('flightmeet/meetups') ?>">Zu den Flugtreffen</a>
    <a class="btn btn-secondary" href="<?= base_url('flightmeet/groups') ?>">Zu den Gruppen</a>
</div>
<?= $this->endSection() ?>
