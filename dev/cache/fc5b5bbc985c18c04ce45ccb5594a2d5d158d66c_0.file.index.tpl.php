<?php
/* Smarty version 3.1.30, created on 2016-12-14 07:16:18
  from "/Users/brent1/Documents/sites/stitcher-demo/src/template/index.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5850e3b20858f1_03361067',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fc5b5bbc985c18c04ce45ccb5594a2d5d158d66c' => 
    array (
      0 => '/Users/brent1/Documents/sites/stitcher-demo/src/template/index.tpl',
      1 => 1481023397,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5850e3b20858f1_03361067 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_meta')) require_once '/Users/brent1/Documents/sites/brendt/stitcher/src/template/smarty/function.meta.php';
if (!is_callable('smarty_function_css')) require_once '/Users/brent1/Documents/sites/brendt/stitcher/src/template/smarty/function.css.php';
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
?>
<html>
    <head>
        <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_14181879395850e3b2072f74_91378446', 'head');
?>

    </head>
    <body>
        <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_5832249385850e3b2084323_78063397', 'body');
?>

    </body>
</html>
<?php }
/* {block 'title'} */
class Block_14047241305850e3b206bfa1_92848547 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
if (isset($_smarty_tpl->tpl_vars['title']->value)) {
echo $_smarty_tpl->tpl_vars['title']->value;?>
 - <?php }?>Stitcher 1.0<?php
}
}
/* {/block 'title'} */
/* {block 'head'} */
class Block_14181879395850e3b2072f74_91378446 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

            <title><?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_14047241305850e3b206bfa1_92848547', 'title', $this->tplIndex);
?>
</title>
            <?php echo smarty_function_meta(array(),$_smarty_tpl);?>

            <?php echo smarty_function_css(array('src'=>"main.scss",'inline'=>true),$_smarty_tpl);?>

        <?php
}
}
/* {/block 'head'} */
/* {block 'header'} */
class Block_16233219265850e3b2078686_73934932 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

                <header>
                    <nav class="wrapper">
                        <a href="/" class="stitcher">Stitcher</a>
                        <a href="/guide">Guide</a>
                        <a href="/examples">Examples</a>
                    </nav>
                </header>
            <?php
}
}
/* {/block 'header'} */
/* {block 'title'} */
class Block_13472655015850e3b207de29_15128589 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

                <?php if (isset($_smarty_tpl->tpl_vars['title']->value)) {?>
                    <div class="wrapper">
                        <h2><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</h2>
                    </div>
                <?php }?>
            <?php
}
}
/* {/block 'title'} */
/* {block 'content'} */
class Block_9731986885850e3b207fbf9_85538629 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'content'} */
/* {block 'footer'} */
class Block_16098106125850e3b2081849_87596948 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'footer'} */
/* {block 'scripts'} */
class Block_4573535215850e3b2083454_42412052 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'scripts'} */
/* {block 'body'} */
class Block_5832249385850e3b2084323_78063397 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

            <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_16233219265850e3b2078686_73934932', 'header', $this->tplIndex);
?>


            <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_13472655015850e3b207de29_15128589', 'title', $this->tplIndex);
?>


            <div class="wrapper">
                <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_9731986885850e3b207fbf9_85538629', 'content', $this->tplIndex);
?>

            </div>

            <footer>
                <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_16098106125850e3b2081849_87596948', 'footer', $this->tplIndex);
?>

            </footer>

            <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_4573535215850e3b2083454_42412052', 'scripts', $this->tplIndex);
?>

        <?php
}
}
/* {/block 'body'} */
}
