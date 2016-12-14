<?php
/* Smarty version 3.1.30, created on 2016-12-06 12:23:18
  from "/Users/brent1/Documents/sites/stitcher-demo/src/template/index.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58469fa67fec47_93756647',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9a2ef203bac9ec640226dfe2d6d318675937d8a9' => 
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
function content_58469fa67fec47_93756647 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_meta')) require_once '/Users/brent1/Documents/sites/stitcher-demo/vendor/brendt/stitcher/src/engine/smarty/function.meta.php';
if (!is_callable('smarty_function_css')) require_once '/Users/brent1/Documents/sites/stitcher-demo/vendor/brendt/stitcher/src/engine/smarty/function.css.php';
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
?>
<html>
    <head>
        <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_201696308458469fa67f0c91_00515334', 'head');
?>

    </head>
    <body>
        <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_170264730458469fa67fde45_38646589', 'body');
?>

    </body>
</html>
<?php }
/* {block 'title'} */
class Block_197595881158469fa67e4550_24842446 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
if (isset($_smarty_tpl->tpl_vars['title']->value)) {
echo $_smarty_tpl->tpl_vars['title']->value;?>
 - <?php }?>Stitcher 1.0<?php
}
}
/* {/block 'title'} */
/* {block 'head'} */
class Block_201696308458469fa67f0c91_00515334 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

            <title><?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_197595881158469fa67e4550_24842446', 'title', $this->tplIndex);
?>
</title>
            <?php echo smarty_function_meta(array(),$_smarty_tpl);?>

            <?php echo smarty_function_css(array('src'=>"main.scss",'inline'=>true),$_smarty_tpl);?>

        <?php
}
}
/* {/block 'head'} */
/* {block 'header'} */
class Block_190282358158469fa67f3c59_39965213 extends Smarty_Internal_Block
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
class Block_60649947258469fa67f8308_94438639 extends Smarty_Internal_Block
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
class Block_163052793458469fa67f9e12_53149781 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'content'} */
/* {block 'footer'} */
class Block_77039201658469fa67fb806_75266231 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'footer'} */
/* {block 'scripts'} */
class Block_46965117458469fa67fd129_70038599 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block 'scripts'} */
/* {block 'body'} */
class Block_170264730458469fa67fde45_38646589 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

            <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_190282358158469fa67f3c59_39965213', 'header', $this->tplIndex);
?>


            <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_60649947258469fa67f8308_94438639', 'title', $this->tplIndex);
?>


            <div class="wrapper">
                <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_163052793458469fa67f9e12_53149781', 'content', $this->tplIndex);
?>

            </div>

            <footer>
                <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_77039201658469fa67fb806_75266231', 'footer', $this->tplIndex);
?>

            </footer>

            <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_46965117458469fa67fd129_70038599', 'scripts', $this->tplIndex);
?>

        <?php
}
}
/* {/block 'body'} */
}
