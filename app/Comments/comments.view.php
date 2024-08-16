<?php /** @var \App\Comments\Comment $comment */ ?>
<?php foreach ($this->comments as $comment): ?>

<div>
    <?= $comment->comment ?>
</div>

<?php endforeach; ?>
