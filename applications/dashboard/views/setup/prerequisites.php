<?php if (!defined('APPLICATION')) exit();

echo $this->Form->Open();
?>
<div class="Title">
   <h1>
      <?php echo Img('applications/dashboard/design/images/vanilla_logo.png', array('alt' => 'Vanilla')); ?>
      <p><?php echo T('Version 2 Installer'); ?></p>
   </h1>
</div>
<div class="Form">
   <?php
      echo $this->Form->Errors(); 
   ?>
   <div class="Button">
      <?php echo Anchor('Try Again', '/dashboard/setup'); ?>
   </div>
</div>
<?php
$this->Form->Close();