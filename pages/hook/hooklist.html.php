<?php GlobalHeader(); ?>

<script type="text/javascript">
$(document).ready(function(){
  $("#selectall").click(function(){
    selectAll(this, "selected_users[]");
  });

  $("#showrolelistlink").click(function(event){
    event.preventDefault();
    if ($("#rolelist").length == 0)
    {
      $.get("rolelist.php", function(data){
        $("body").append(data);
        $("#rolelist").dialog({width:750, height:450});
      });
    }
    else
    {
      $("#rolelist").dialog();
    }
  });
});
</script>

<h1><?php Translate("Hook management"); ?></h1>

<p class="hdesc"><?php Translate("Here you can see a list of all users which can be authenticated by your subversion server."); ?></p>

<?php HtmlFilterBox("hooklist", 1); ?>

<form action="hooklist.php" method="POST">
	<table id="hooklist" class="datatable">

	<thead>
	<tr>
	  <th width="20">
	  	<?php if (HasAccess(ACL_MOD_USER, ACL_ACTION_DELETE) || HasAccess(ACL_MOD_ROLE,	ACL_ACTION_ASSIGN)) { ?>
	    <input type="checkbox" id="selectall">
	    <?php } ?>
	  </th>
	  <th>
	  	<?php Translate("Name"); ?>
	  </th>
	  <th>
	  	<?php Translate("Author"); ?>
	  </th>
	  <th>
	  	<?php Translate("Type"); ?>
	  </th>
	</tr>
	</thead>

	<tfoot>
	<tr>
	  <td colspan="2">

	    <table class="datatableinline">
	      <colgroup>
	        <col width="50%">
	        <col width="50%">
	      </colgroup>
	      <tr>
	        <td>
	          <?php if (HasAccess(ACL_MOD_HOOKS, ACL_ACTION_DELETE)) { ?>
	          <input type="submit" name="delete" value="<?php Translate("Delete"); ?>" class="delbtn" onclick="return deletionPrompt('<?php Translate("Are you sure?"); ?>');">
	          <?php } ?>
	        </td>
	      </tr>
	    </table>

	  </td>
	</tr>
	</tfoot>

	<tbody>
		<?php foreach (GetArrayValue('HookList') as $hook) { ?>
		<tr>
		    <td>
                <?php if (HasAccess(ACL_MOD_USER, ACL_ACTION_DELETE) || HasAccess(ACL_MOD_ROLE,	ACL_ACTION_ASSIGN)) { ?>
                <input type="checkbox" name="selected_hooks[]" value="<?php print($hook->id); ?>">
                <?php } ?>
            </td>
            <td><a href="hookview.php?h=<?php print($hook->id); ?>"><?php print($hook->title); ?></a></td>
            <td><?php print($hook->author); ?></td>
            <td><?php print($hook->type); ?></td>
        </tr>
		<?php } ?>
	</tbody>
	</table>
</form>

<?php GlobalFooter(); ?>