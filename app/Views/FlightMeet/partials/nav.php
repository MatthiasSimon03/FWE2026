<nav class="fm-nav" aria-label="Hauptnavigation">
    <div class="fm-brand">
        <a class="fm-logo" href="<?= base_url('flightmeet') ?>">FlightMeet</a>
        <button class="fm-toggle" id="fmMenuToggle" type="button" aria-expanded="false" aria-controls="fmMenu">
            Menü
        </button>
    </div>

    <ul class="fm-menu" id="fmMenu">
        <li class="<?= ($active ?? '') === 'home' ? 'active' : '' ?>">
            <a href="<?= base_url('flightmeet') ?>">Home</a>
        </li>
        <li class="<?= ($active ?? '') === 'meetups' ? 'active' : '' ?>">
            <a href="<?= base_url('flightmeet/meetups') ?>">Flugtreffen</a>
        </li>
        <li class="<?= ($active ?? '') === 'groups' ? 'active' : '' ?>">
            <a href="<?= base_url('flightmeet/groups') ?>">Gruppen</a>
        </li>
        <li class="<?= ($active ?? '') === 'chat' ? 'active' : '' ?>">
            <a href="<?= base_url('flightmeet/chat') ?>">Chat</a>
        </li>
    </ul>
</nav>

