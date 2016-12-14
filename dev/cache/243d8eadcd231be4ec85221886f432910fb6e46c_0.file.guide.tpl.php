<?php
/* Smarty version 3.1.30, created on 2016-12-14 07:16:18
  from "/Users/brent1/Documents/sites/stitcher-demo/src/template/guide.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5850e3b2055bd4_25613172',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '243d8eadcd231be4ec85221886f432910fb6e46c' => 
    array (
      0 => '/Users/brent1/Documents/sites/stitcher-demo/src/template/guide.tpl',
      1 => 1481695351,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:index.tpl' => 1,
  ),
),false)) {
function content_5850e3b2055bd4_25613172 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_js')) require_once '/Users/brent1/Documents/sites/brendt/stitcher/src/template/smarty/function.js.php';
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_12797392865850e3b2040e66_26190269', 'content');
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_6487836905850e3b20533f9_35199102', 'scripts');
?>

<?php $_smarty_tpl->inheritance->endChild();
$_smarty_tpl->_subTemplateRender("file:index.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 2, false);
}
/* {block 'content'} */
class Block_12797392865850e3b2040e66_26190269 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <?php echo $_smarty_tpl->tpl_vars['content']->value;?>


    <ul>
        <li><a href="/guide/setting-up">Setting up</a></li>
        <li><a href="/guide/project-structure">Project Structure</a></li>
        <li><a href="/guide/working-with-data">Working with data</a></li>
        <li><a href="/guide/working-with-images">Working with images</a></li>
        <li><a href="/guide/helper-functions">Helper functions</a></li>
    </ul>
<?php
}
}
/* {/block 'content'} */
/* {block 'scripts'} */
class Block_6487836905850e3b20533f9_35199102 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <?php echo smarty_function_js(array('src'=>"js/codeClick.js",'inline'=>true),$_smarty_tpl);?>

<?php
}
}
/* {/block 'scripts'} */
}
