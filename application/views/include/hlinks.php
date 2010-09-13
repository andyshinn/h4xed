<div id="hlinks">
<?php echo anchor('', "<img class=\"logo\" src=\"" . base_url() . "assets/images/header-04-f.png\" />") ?>

    <ul>
        <li>
            <a href="<?php echo site_url('radio/news')?>"><span class="orange">home</span></a>
        </li>
        <li>
            <a href="<?php echo site_url('radio/tunein')?>">tune<span class="orange">in</span></a>
        </li>
        <li>
            <a href="<?php echo site_url('playlist')?>">play<span class="orange">list</span></a>
        </li>
        <li>
            <a href="<?php echo site_url('radio/contact') ?>">contact<span class="orange">us</span></a>
        </li>
    </ul>
</div>

<div id="dev_msg"><?php echo $dev_msg ?></div>