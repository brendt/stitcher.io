<?php
/**
 * @var string $name
 * @var string|null $label
 * @var string|null $id
 * @var string|null $type
 * @var string|null $default
 */

use Tempest\Http\Session\Session;
use Tempest\Validation\Validator;

use function Tempest\get;
use function Tempest\Support\str;

/** @var Session $session */
$session = get(Session::class);

/** @var Validator $validator */
$validator = get(Validator::class);

$label ??= str($name)->title();
$id ??= $name;
$type ??= 'text';
$default ??= null;

$errors = $session->getErrorsFor($name);
$original = $session->getOriginalValueFor($name, $default);
?>

<div class="grid gap-1">
    <label :for="$id">{{ $label }}</label>

    <textarea :if="$type === 'textarea'" :name="$name" :id="$id" class="border bg-white border-gray-500 p-2 rounded-md">{{ $original ?? $value }}</textarea>
    <input :else :type="$type" :name="$name" :id="$id" :value="$original" class="border bg-white border-gray-500 p-2 rounded-md"/>

    <div>
        <ul :if="$errors !== []">
            <li :foreach="$errors as $error">
                {{ $validator->getErrorMessage($error) }}
            </li>
        </ul>
    </div>
</div>
