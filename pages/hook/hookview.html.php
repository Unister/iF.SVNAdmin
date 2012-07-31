<?php GlobalHeader(); ?>
<?php $hook = GetValue('Hook');?>
<!--
  Javascript
-->
<script type="text/javascript">
$(document).ready(function(){
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

<h1><?php Translate('Hookmanagment'); ?></h1>
<p class="hdesc"><?php Translate('On this page you can edit the selected hook.'); ?></p>

<h2><?php Translate('Hook: ' . $hook->getTitle())?></h2>
<form action="hookview.php?h=<?php print($hook->getId())?>" method="post">
    <label><b><?php Translate('Content'); ?></b></label><br/>
    <textarea name="content" rows="20" cols="200" class="lineedit"><?php print($hook->getContent())?></textarea><br />
    <?php if (HasAccess(ACL_MOD_HOOKS, ACL_ACTION_ADD)) { ?>
    <input type="submit" name="update" value="<?php Translate('Update'); ?>" class="delbtn" onclick="return deletionPrompt('<?php Translate('Are u sure'); ?>');" />
    <?php }?>
</form>


<?php GlobalFooter(); ?>