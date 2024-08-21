<?php /** @var \App\Comments\Comment $comment */

use App\Auth\OauthController;
use function Tempest\uri; ?>

<?php foreach ($this->comments as $comment): ?>

<x-form :if="$this->user">
    <x-input type="textarea" name="comment"></x-input>
    <x-submit />
</x-form>
<div :else>
    <a href="<?= uri(OauthController::class) . "?back={$this->back}#comments" ?>">Log in with google</a>
</div>

<div class="comment">
    <p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>
    <p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>

    <small class="credits">
        Written by <?= $comment->user->name ?> on <?= $comment->createdAt->format('F d, Y') ?>
    </small>
</div>

<?php endforeach; ?>
