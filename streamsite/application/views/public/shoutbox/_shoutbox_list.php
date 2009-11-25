<div id="shoutbox" class="box">
  <form id="form" name="form" method="post" action="">
    <div id="messages">
      <dl>{shout}
        <dt>{name} - {date}</dt>
        <dd>{message}</dd>
      {/shout}
	  </dl>
    </div>
    <div id="button">
        <input name="Post" type="button" value="Post" />
      </div>
      <div id="fields">
        <input name="name" type="text" value="Name" maxlength="30" />
        <textarea name="message" cols="18">Your message!</textarea>
      </div>
  </form>
</div>