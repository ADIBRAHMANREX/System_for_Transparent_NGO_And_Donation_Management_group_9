<?php
declare(strict_types=1);

function view(string $name, array $data = []): void {
  extract($data);
  require __DIR__ . "/../views/{$name}.php";
}
