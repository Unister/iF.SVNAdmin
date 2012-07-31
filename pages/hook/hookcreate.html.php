<?php GlobalHeader(); ?>

<h1><?php Translate('Create hook'); ?></h1>
<p class="hdesc"><?php Translate('Create a new hook. Set @title and @author in hook content to indentify hook'); ?></p>

<div>
  <form method="POST" action="hookcreate.php">

    <div>
        <label>Type</label><br/>
        <select name="type" >
            <option value="0"><?php Translate('Choose a hook type'); ?></option>
            <option value="precommit">Pre-Commit</option>
            <option value="postcommit">Post-Commit</option>
        </select>
    </div>
    <div class="form-field">
      <label for="hookname"><?php Translate('Filename'); ?></label>
      <input type="text" name="hookfilename" id="hookfilename" class="lineedit">
    </div>

    <div class="form-field">
      <label for="hookcontent"><?php Translate('Content') ?></label><br>
      <textarea name="hookcontent" id="hookcontent" rows="20" cols="200" class="lineedit">
#@title
#@author
      </textarea>
    </div>

    <div class="formsubmit">
      <input type="submit" name="create" value="<?php Translate('Create'); ?>" class="addbtn">
    </div>
  </form>

  <p>
    <a href="hooklist.php">&#xAB; <?php Translate('Back to overview'); ?></a>
  </p>

</div>

<?php GlobalFooter(); ?>