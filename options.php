<script src="../wp-content/plugins/hookpress/jquery.livequery.js"></script>
<script type='text/javascript'>

var getHooks = function getHooks(editornew) {
  // editornew = edit or new
  var form = jQuery('#'+editornew+'form');
  var type = form.find('.type:checked').attr('id');
  if (type == 'action') {
    form.find('.action_or_filter').text('<?php _e("Action:",'hookpress');?> ');
    form.find('.filtermessage').hide();
  }
  if (type == 'filter') {
    form.find('.action_or_filter').text('<?php _e("Filter:",'hookpress');?> ');
    form.find('.filtermessage').show();
  }
	jQuery.ajax({type:'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_get_hooks&type='+type,
    beforeSend:function(){jQuery('#newhook').html('<img src="../wp-content/plugins/hookpress/i/spin.gif" alt="loading..."/>')},
    success:function(html){
      form.find('.hook').html(html);
      getFields(editornew);
    },
    dataType:'html'}
	)
}

var getFields = function getFields(editornew) {
  // editornew = edit or new
  var form = jQuery('#'+editornew+'form');
  var hook = form.find('.hook').val();
  var type = form.find('.type:checked').attr('id');
	jQuery.ajax({type:'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_get_fields&hook='+hook+'&type='+type,
    beforeSend:function(){form.find('.fields').html('<img src="../wp-content/plugins/hookpress/i/spin.gif" alt="loading..."/>')},
    success:function(html){
      form.find('.fields').html(html)
    },
    dataType:'html'}
	)
};

var editSubmit = function editSubmit() {
  var form = jQuery('#editform');

  if (!form.find('.fields').val()) {
    form.find('.indicator').html('<small><?php _e("You must select at least one field to send.","hookpress");?></small>');
    return;
  }
  if (!/^https?:\/\/\w+/.test(jQuery('#editurl').val())) {
    form.find('.indicator').html('<small><?php _e("Please enter a valid URL.","hookpress");?></small>');
    return;
  }

  form.find('.indicator').html('<img src="<?php echo admin_url( 'images/loading.gif' ); ?>" alt="loading..."/>');

	id = form.find('.hook-id').val();
	
  jQuery.ajax({type: 'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_add_fields'
         +'&fields='+form.find('.fields').val().join()
         +'&url='+form.find('.url').val()
         +'&type='+form.find('.type:checked').attr('id')
         +'&hook='+form.find('.hook').val()
         +'&enabled='+form.find('#enabled').val()
         +'&id='+id
         +'&_nonce='+form.find('#submit-nonce').val(),
    beforeSend:function(){
      form.find('.submit').hide();
      form.find('.cancel').hide()
    },
    success:function(html){
      form.find('.submit').show();
      form.find('.cancel').show()
      form.find('.indicator').html('');
      if (/^ERROR/.test(html))
        form.find('.indicator').html(html);
      else if (!html)
        form.find('.indicator').html('<?php _e("There was an unknown error.","hookpress");?>');
      else {
        jQuery('#'+id).replaceWith(html);
        tb_init('a.thickbox, area.thickbox, input.thickbox');
        tb_remove();
      }
    },
    dataType:'html'}
  );
};

var enforceFirst = function enforceFirst(editornew) {
  // editornew = edit or new
  var form = jQuery('#'+editornew+'form');
  var type = form.find('.newtype:checked').attr('id');
  if (type == 'action')
    return;
  form.find('option.first').attr('selected','true');
}

var newSubmit = function newSubmit() {
  var form = jQuery('#editform');
  if (!form.find('.fields').val()) {
    form.find('.indicator').html('<small><?php _e("You must select at least one field to send.","hookpress");?></small>');
    return;
  }
  if (!/^https?:\/\/\w+/.test(jQuery('#newurl').val())) {
    form.find('.indicator').html('<small><?php _e("Please enter a valid URL.","hookpress");?></small>');
    return;
  }

  form.find('.indicator').html('<img src="../wp-content/plugins/hookpress/i/spin.gif" alt="loading..."/>');

  jQuery.ajax({type: 'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_add_fields'
         +'&fields='+form.find('.fields').val().join()
         +'&url='+form.find('.url').val()
         +'&type='+form.find('.type:checked').attr('id')
         +'&hook='+form.find('.hook').val()
         +'&_nonce='+form.find('#submit-nonce').val(),
    beforeSend:function(){
      form.find('.submit').hide();
      form.find('.cancel').hide()
    },
    success:function(html){
      form.find('.submit').show();
      form.find('.cancel').show()
      form.find('.indicator').html('');
      if (/^ERROR/.test(html))
        form.find('.indicator').html(html);
      else if (!html)
        form.find('.indicator').html('<?php _e("There was an unknown error.","hookpress");?>');
      else {
        var newhook = jQuery(html);
        newhook.css('background-color','rgb(255, 251, 204)');
        newhook.appendTo(jQuery('#webhooks'));
        tb_init('a.thickbox, area.thickbox, input.thickbox');
        tb_remove();
/*        setEvents(); */
        newhook.animate({backgroundColor:'white'},2000,null,
          function(){newhook.css('background-color','transparent')});
      }
    },
    dataType:'html'}
  );
};

var deleteHook = function deleteHook(id) {
	var nonce = jQuery('#delete-nonce-' + id).val();
  jQuery.ajax({type: 'POST',
    url:'admin-ajax.php',
	beforeSend:function(){jQuery('#' + id + ' span.edit').html('<img src="<?php echo admin_url( 'images/loading.gif' ); ?>" alt=" "/>')},
    data:'action=hookpress_delete_hook&id='+id + '&_nonce=' +nonce,
    success:function(html){
      if (/^ERROR/.test(html))
        jQuery('#message').html(html);
      else {
        jQuery('#'+id).fadeOut('fast',function(){jQuery('#'+id).remove()});
      }
    },
    dataType:'html'}
  );
}

var setHookEnabled = function setHookEnabled(id, nonce, boolean) {
  jQuery.ajax({type: 'POST',
    url:'admin-ajax.php',
	beforeSend:function(){jQuery('#' + id + ' span.edit').html('<img src="<?php echo admin_url( 'images/loading.gif' ); ?>" alt=" "/>')},
    data:'action=hookpress_set_enabled&id='+id+'&_nonce='+nonce+'&enabled='+boolean,
    success:function(html){
      if (/^ERROR/.test(html))
        jQuery('#message').html(html);
      else {
        jQuery('#'+id).fadeOut('fast',function(){
          jQuery('#'+id).replaceWith(html);
        	tb_init('a.thickbox, area.thickbox, input.thickbox');
/*          setEvents(); */
        });
      }
    },
    dataType:'html'}
  );
}

var setupEditHook = function setupEditHook(id) {
  jQuery.ajax({type: 'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_edit_hook&id='+id,
    success:function(html){
      jQuery('#TB_ajaxContent').html(html);
      jQuery('#editform .hook').change(function(){getFields('old')});
	  jQuery('#editform .fields').change(function(){enforceFirst('old')});
	  jQuery('#editform .submit').click(editSubmit);
	  jQuery('#editform .cancel').click(tb_remove);
    },
    dataType:'html'}
  );
}

jQuery(document).ready(function(){
  // initial setup
  getHooks('new');
  // set event handler
  setEvents();

  // setup version check
  var version = jQuery('#hookpress-version').html();
  var json = <?php echo hookpress_check_version_json($hookpress_version); ?>;
  if (json.result == 'newbeta')
      jQuery('#hookpress-version').addClass('updated').html(<?php echo "'<p>".str_replace('VERSION',"'+json.beta.version+'",str_replace('<A>',"<a href=\"'+json.beta.url+'\">",addslashes(__("There is a new beta (VERSION) of HookPress. You can <A>download it here</a> at your own risk.","hookpress"))))."</p>'"?>).show();
  if (json.result == 'new')
      jQuery('#hookpress-version').addClass('updated').html(<?php echo "'<p>".str_replace('VERSION',"'+json.current.version+'",str_replace('<A>',"<a href=\"'+json.current.url+'\">",addslashes(__("There is a new version (VERSION) of HookPress available! You can <A>download it here</a>.","hookpress"))))."</p>'"?>).show();
});

var setEvents = function setEvents() {
  // new form:
  jQuery('#newform .type').livequery('change',function(){getHooks('old')});
  jQuery('#newform .hook').change(function(){getFields('new')});
  jQuery('#newform .fields').change(function(){enforceFirst('new')});
  jQuery('#newform .submit').click(newSubmit);
  jQuery('#newform .cancel').click(tb_remove);

  jQuery('.delete').livequery('click', function(e){
    var id = e.currentTarget.id.replace('delete','');
    deleteHook(id);
  });
  jQuery('.edit').livequery('click', function(e){
    var id = e.currentTarget.id.replace('edit','');
    if(id){setupEditHook(id);}
  });

  jQuery('.on').livequery('click', function(e){
    var id = e.currentTarget.id.replace('on','');
    var nonce = jQuery('#action-nonce-' + id).val();
    if(id && nonce){setHookEnabled(id, nonce, 'false');}
  });
  jQuery('.off').livequery('click', function(e){
    var id = e.currentTarget.id.replace('off','');
    var nonce = jQuery('#action-nonce-' + id).val();
    if(id&&nonce){setHookEnabled(id, nonce, 'true');}
  });
}

</script>

<div class="wrap">
		<h2>
			<?php _e('HookPress','hookpress');?> <small><?php 
			
			$display_version = $hookpress_version;
			$split = explode('.',$display_version);
			if (strlen($split[1]) != 1) {
				$pos = strpos($display_version,'.')+2;
				$display_version = substr($display_version,0,$pos).'.'.substr($display_version,$pos);
			}
      echo $display_version;
			?></small>
		</h2>

	<?php echo "<div id='hookpress-version' style='display:none;'>$hookpress_version</div>"; ?>
		
	<form method="post">

			<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%2fhookpress%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=1&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8' target='_new'><img src="https://www.paypal.com/<?php echo hookpress_paypal_directory(); ?>i/btn/btn_donate_SM.gif" name="submit" alt="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal');?>" title="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal','hookpress');?>" style="float:right" /></a>

	<p><small><?php _e('by <a href="http://mitcho.com/">mitcho (Michael 芳貴 Erlewine)</a>','hookpress');?>. <?php _e('Follow <a href="http://twitter.com/hookpress/">HookPress</a> on Twitter.','hookpress');?></small></p>

	<h3><?php _e("Webhooks","hookpress");?></h3>
<?php echo hookpress_print_webhooks_table();?>

  <p><input class="thickbox button" type="button" value="<?php _e("Add webhook",'hookpress');?>" title="<?php _e('Add new webhook','hookpress');?>" alt="#TB_inline?height=330&width=500&inlineId=hookpress-webhook"/></p>

<!--	<h3>General options</h3>
	
	<div>
		<p class="submit">
			<input type="submit" name="update_hookpress" value="<?php _e("Update options",'hookpress')?>" />
			<input type="submit" onclick='return confirm("<?php _e("Do you really want to reset your configuration?",'hookpress');?>");' class="hookpress_warning" name="reset_hookpress" value="<?php _e('Reset options','hookpress')?>" />
		</p>
	</div>-->
		
</form>

<div id='hookpress-webhook' style='display:none;'>
<form id='newform'>
<table>
<tr><td><label style='font-weight: bold' for='newhook'><?php _e("WordPress hook type",'hookpress');?>: </label></td><td><input type='radio' id='action' class='type newtype' name='newtype' checked='checked'> <?php _e("action","hookpress");?></input> <input type='radio' id='filter' class='type newtype' name='newtype'> <?php _e("filter","hookpress");?></input></td></tr>
<tr>
<td><label style='font-weight: bold' for='newhook' class='action_or_filter'></label></td>
<td><select name='newhook' class='hook' id='newhook'></select></td></tr>
<tr><td style='vertical-align: top'><label style='font-weight: bold' for='newfields'><?php _e("Fields",'hookpress');?>: </label><br/><small><?php _e("Ctrl-click on Windows or Command-click on Mac to select multiple. The <code>hook</code> field with the relevant hook name is always sent.");?></small><br/><span class='filtermessage'><small><?php _e('The first argument of a filter must always be sent and should be returned by the webhook, with modification.','hookpress');?></small></span></td><td><select style='vertical-align: top' name='newfields' class='fields' id='newfields' multiple='multiple' size='8'>
  </select></td></tr>
<tr><td><label style='font-weight: bold' for='newurl'><?php _e("URL",'hookpress');?>: </label></td><td><input name='newurl' class='url' id='newurl' size='40' value='http://'></input></td></tr>
</table>
<?php  echo "<input type='hidden' id='submit-nonce' name='submit-nonce' value='" . wp_create_nonce( 'submit-webhook') . "' />"; ?>
  <center><span id='newindicator' class='indicator'></span><br/>
  <input type='button' class='button' id='newsubmit' class='submit' value='<?php _e('Add new webhook','hookpress');?>'/>
  <input type='button' class='button' id='newcancel' class='cancel' value='<?php _e('Cancel');?>'/></center>

</form>
</div>