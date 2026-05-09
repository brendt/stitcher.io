<?php
/** @var null|string $label The submit button's label */
?>

<input type="submit" :value="$label ?? 'Submit'" class="border-2 border-primary bg-primary text-white rounded-full p-2 px-4 hover:bg-pastel hover:text-primary font-bold cursor-pointer">
