<?= $this->load->view('include/header.php') ?>
<?= $this->load->view('include/menu.php') ?>
<div id="page">
    <div class="width30 floatLeft leftColumn">
        <h1>Station Info</h1>
        <h2>Tune-in Links</h2>
        <p>
            {tuneInSC} {tuneInWM} 
        </p>
        <h2>Currently Playing</h2>
        <p>
            {songCurrent} 
        </p>
        <p>
            {songCurrentAd} 
        </p>
        <h2>Player</h2>
        <p>
            <script type="text/javascript" src="/js/player.js">
            </script>
        </p>
        <h2>History</h2>
        <ul>
            {songHistory}
            <li>
                {song}
            </li>
            {/songHistory}
        </ul>
    </div>
    <div class="width70 floatRight">
        <div class="gradient">
            <h1>H4XED Radio</h1>
            <h2>H4XED Metal</h2>
            <p>
                H4XED Metal is a H4XED Radio streaming internet radio station. We bring you the newest in Melodic Death Metal and Metalcore. Please feel free to <a href="{contactUrl}">Contact Us</a>
                with you comments and suggestions!
            </p>
            <h2>Hourly Listener Graph</h2>
            <p>
                Top listeners per hour spanning the last 8 hours. Time is in PST.<img src="{graphHourlyUrl}" alt="Hourly Listeners" />
            </p><h2>Daily Listener Graph</h2>
            <p>
                Top listeners per day spanning the last month.
            </p>
        </div>
    </div>
</div>
</div>
<?= $this->load->view('include/footer.php') ?>
