<?php
/* Smarty version 3.1.30, created on 2016-12-06 09:28:58
  from "/Users/brent1/Documents/sites/stitcher-demo/src/template/examples/detail.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_584676cab39d10_57497420',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c3b87a912f3814161779aa1cc9349cbca13d4a21' => 
    array (
      0 => '/Users/brent1/Documents/sites/stitcher-demo/src/template/examples/detail.tpl',
      1 => 1480918475,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:index.tpl' => 1,
  ),
),false)) {
function content_584676cab39d10_57497420 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_1452792408584676cab38b76_26762168', 'content');
?>

<?php $_smarty_tpl->inheritance->endChild();
$_smarty_tpl->_subTemplateRender("file:index.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 2, false);
}
/* {block 'content'} */
class Block_1452792408584676cab38b76_26762168 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <h2><?php echo $_smarty_tpl->tpl_vars['example']->value['title'];?>
</h2>

    <?php if (isset($_smarty_tpl->tpl_vars['example']->value['image'])) {?>
        <img src="<?php echo $_smarty_tpl->tpl_vars['example']->value['image']['src'];?>
" srcset="<?php echo $_smarty_tpl->tpl_vars['example']->value['image']['srcset'];?>
" <?php if (isset($_smarty_tpl->tpl_vars['example']->value['image']['alt'])) {?>alt="<?php echo $_smarty_tpl->tpl_vars['example']->value['image']['alt'];?>
"<?php }?>>
    <?php }?>

    <?php echo $_smarty_tpl->tpl_vars['example']->value['body'];?>


    <a href="/examples">Back</a>
<?php
}
}
/* {/block 'content'} */
}
