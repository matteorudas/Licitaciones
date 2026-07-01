<?php
namespace App\Core;

class Validator
{
    private array $errors = [];

    public function required(mixed $value, string $field): self
    {
        if ($value === null || trim((string)$value) === '') {
            $this->errors[$field][] = "El campo $field es obligatorio.";
        }
        return $this;
    }

    public function maxLength(mixed $value, string $field, int $max): self
    {
        if (strlen((string)$value) > $max) {
            $this->errors[$field][] = "El campo $field no puede superar $max caracteres.";
        }
        return $this;
    }

    public function inList(mixed $value, string $field, array $list): self
    {
        if (!in_array($value, $list, true)) {
            $this->errors[$field][] = "El campo $field debe ser uno de: " . implode(', ', $list);
        }
        return $this;
    }

    public function decimal(mixed $value, string $field): self
    {
        if (!preg_match('/^\d+(\.\d{1,2})?$/', (string)$value) || (float)$value <= 0) {
            $this->errors[$field][] = "El campo $field debe ser un número mayor a 0 con máximo 2 decimales.";
        }
        return $this;
    }

    public function date(mixed $value, string $field): self
    {
        $d = \DateTime::createFromFormat('Y-m-d', (string)$value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            $this->errors[$field][] = "El campo $field debe tener formato YYYY-MM-DD.";
        }
        return $this;
    }

    public function time(mixed $value, string $field): self
    {
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string)$value)) {
            $this->errors[$field][] = "El campo $field debe tener formato HH:mm.";
        }
        return $this;
    }

    public function dateTimeOrder(
        string $fechaInicio, string $horaInicio,
        string $fechaCierre, string $horaCierre
    ): self {
        $inicio = strtotime("$fechaInicio $horaInicio");
        $cierre = strtotime("$fechaCierre $horaCierre");
        if ($inicio !== false && $cierre !== false && $inicio >= $cierre) {
            $this->errors['fecha_cierre'][] = 'La fecha/hora de cierre debe ser posterior a la de inicio.';
        }
        return $this;
    }

    public function fails(): bool   { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
}