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
        <a class="btn btn-secondary" href="https://leonardrichertz.github.io/vario/dev/"> Zum Variometer (extern)</a>
    </div>

    <!-- DIAGRAMM-BEREICH (Schnittstelle für externes JS über data-stats) -->
    <div class="fm-dashboard-chart-card">
        <h3>Geplante Flugtreffen nach Regionen</h3>
        <!-- esc(..., 'attr') schützt das HTML-Attribut vor Injections -->
        <canvas id="regionChart" data-stats="<?= esc(json_encode($regionStats), 'attr') ?>"></canvas>
    </div>

    <!-- Chart.js via CDN laden -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?= $this->endSection() ?>