<?php
/* Smarty version 3.1.30, created on 2016-12-06 07:40:29
  from "/Users/brent1/Documents/sites/stitcher-demo/src/template/examples/overview.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58465d5d987189_18808275',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9d9cb9d5c209c1ab0cc85cc642e1246e0f7f2a1b' => 
    array (
      0 => '/Users/brent1/Documents/sites/stitcher-demo/src/template/examples/overview.tpl',
      1 => 1480918475,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:index.tpl' => 1,
  ),
),false)) {
function content_58465d5d987189_18808275 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_172109753858465d5d985e86_03093328', 'content');
?>

<?php $_smarty_tpl->inheritance->endChild();
$_smarty_tpl->_subTemplateRender("file:index.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 2, false);
}
/* {block 'content'} */
class Block_172109753858465d5d985e86_03093328 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['collection']->value, 'entry');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['entry']->value) {
?>
        <h2><?php echo $_smarty_tpl->tpl_vars['entry']->value['title'];?>
</h2>
        <p><?php echo $_smarty_tpl->tpl_vars['entry']->value['intro'];?>
</p>
        <a href="/examples/<?php echo $_smarty_tpl->tpl_vars['entry']->value['id'];?>
">Read more</a>
    <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>


    <div>
        <?php if (isset($_smarty_tpl->tpl_vars['pagination']->value['previous'])) {?>
            <a href="<?php echo $_smarty_tpl->tpl_vars['pagination']->value['previous']['url'];?>
">Previous page (<?php echo $_smarty_tpl->tpl_vars['pagination']->value['previous']['index'];?>
)</a>
        <?php }?>
        <?php if (isset($_smarty_tpl->tpl_vars['pagination']->value['next'])) {?>
            <a href="<?php echo $_smarty_tpl->tpl_vars['pagination']->value['next']['url'];?>
">Next page (<?php echo $_smarty_tpl->tpl_vars['pagination']->value['next']['index'];?>
)</a>
        <?php }?>
    </div>
<?php
}
}
/* {/block 'content'} */
}
