<?php
/* Smarty version 3.1.30, created on 2016-12-06 07:40:26
  from "/Users/brent1/Documents/sites/stitcher-demo/src/template/home.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58465d5a452c02_82840621',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '71d57aa6c660ff648f1d4adb89f3725e0b8bd8c2' => 
    array (
      0 => '/Users/brent1/Documents/sites/stitcher-demo/src/template/home.tpl',
      1 => 1480918475,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:index.tpl' => 1,
  ),
),false)) {
function content_58465d5a452c02_82840621 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_css')) require_once '/Users/brent1/Documents/sites/stitcher-demo/vendor/brendt/stitcher/src/engine/smarty/function.css.php';
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_120978401358465d5a4494f6_41869776', 'head');
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_47358222258465d5a44bfe0_45599572', 'header');
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_191159216758465d5a450280_75558195', 'body');
?>

<?php $_smarty_tpl->inheritance->endChild();
$_smarty_tpl->_subTemplateRender("file:index.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 2, false);
}
/* {block 'head'} */
class Block_120978401358465d5a4494f6_41869776 extends Smarty_Internal_Block
{
public $append = true;
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <?php echo smarty_function_css(array('src'=>'home.scss','inline'=>true),$_smarty_tpl);?>

<?php
}
}
/* {/block 'head'} */
/* {block 'header'} */
class Block_47358222258465d5a44bfe0_45599572 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'header'} */
/* {block 'body'} */
class Block_191159216758465d5a450280_75558195 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

    <div class="heading">
        <h2>
            Welcome to Stitcher
        </h2>
        <h3>
            a tool to create <em>blazing</em> fast websites.
        </h3>

        <div class="vwrapper">
            <a class="button" href="./guide">Read the guide</a>
            <em class="button-link">or</em>
            <a class="button" href="./examples">Show examples</a>
        </div>
    </div>
<?php
}
}
/* {/block 'body'} */
}
