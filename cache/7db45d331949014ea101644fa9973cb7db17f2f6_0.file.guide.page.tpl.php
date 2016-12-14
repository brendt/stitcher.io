<?php
/* Smarty version 3.1.30, created on 2016-12-06 12:23:29
  from "/Users/brent1/Documents/sites/stitcher-demo/src/template/guide.page.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58469fb1a4a557_11496864',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7db45d331949014ea101644fa9973cb7db17f2f6' => 
    array (
      0 => '/Users/brent1/Documents/sites/stitcher-demo/src/template/guide.page.tpl',
      1 => 1481023408,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:index.tpl' => 1,
  ),
),false)) {
function content_58469fb1a4a557_11496864 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_js')) require_once '/Users/brent1/Documents/sites/stitcher-demo/vendor/brendt/stitcher/src/engine/smarty/function.js.php';
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_113021675358469fb1a228a6_62943759', 'content');
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_18700137358469fb1a3f8a5_41899903', 'footer');
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_132927466058469fb1a49533_60203925', 'scripts');
?>

<?php $_smarty_tpl->inheritance->endChild();
$_smarty_tpl->_subTemplateRender("file:index.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 2, false);
}
/* {block 'content'} */
class Block_113021675358469fb1a228a6_62943759 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <?php if (isset($_smarty_tpl->tpl_vars['content']->value)) {?>
        <?php echo $_smarty_tpl->tpl_vars['content']->value;?>

    <?php }
}
}
/* {/block 'content'} */
/* {block 'footer'} */
class Block_18700137358469fb1a3f8a5_41899903 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <div class="wrapper">
        <?php if (isset($_smarty_tpl->tpl_vars['prevUrl']->value)) {?>
            <a class="prev" href="<?php echo $_smarty_tpl->tpl_vars['prevUrl']->value;?>
">Previous<?php if (isset($_smarty_tpl->tpl_vars['prevTitle']->value)) {?>: <?php echo strtolower($_smarty_tpl->tpl_vars['prevTitle']->value);
}?></a>
        <?php }?>
        <?php if (isset($_smarty_tpl->tpl_vars['nextUrl']->value)) {?>
            <a class="next" href="<?php echo $_smarty_tpl->tpl_vars['nextUrl']->value;?>
">Next<?php if (isset($_smarty_tpl->tpl_vars['nextTitle']->value)) {?>: <?php echo strtolower($_smarty_tpl->tpl_vars['nextTitle']->value);
}?></a>
        <?php }?>
    </div>
<?php
}
}
/* {/block 'footer'} */
/* {block 'scripts'} */
class Block_132927466058469fb1a49533_60203925 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <?php echo smarty_function_js(array('src'=>"js/codeClick.js",'inline'=>true),$_smarty_tpl);?>

<?php
}
}
/* {/block 'scripts'} */
}
