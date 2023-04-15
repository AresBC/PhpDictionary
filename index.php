<?php declare(strict_types=1);


enum Type: string
{
    case String = 'string';
    case Integer = 'integer';
    case Float = 'float';
    case Bool = 'bool';
    case Object = 'object';
    case Null = 'null';
    case Callable = 'callable';
    case Resource = 'resource';
    case Mixed = 'mixed';
    case Undefined = 'undefined';

    public function equalType($item): bool
    {
        return $this === Type::Mixed || $this->value === gettype($item);
    }
}


class KeyValuePair
{
    function __construct(
        public $key,
        public $value,
        public $before = null,
        public $next = null,
    )
    {
    }
}

class Dictionary implements Iterator
{
    private ?KeyValuePair $current = null;
    private ?KeyValuePair $last = null;


    function __construct(
        private Type $keyType = Type::Mixed,
        private Type $valueType = Type::Mixed
    )
    {
    }

    /**
     * @throws Exception
     */
    function add($key, $value): void
    {
        if (!$this->keyType->equalType($key) || !$this->valueType->equalType($value)) {
            throw new Exception(self::class . "<{$this->keyType->name},{$this->valueType->name}> given " . gettype($value));
        }
        $kvp = new KeyValuePair($key, $value, $this->last, null);
        if ($this->current === null) $this->current = $kvp;
        if ($this->last) $this->last->next = $kvp;
        $this->last = $kvp;
    }


    /**
     * @throws Exception
     */
    function get($key)
    {
        /** @var KeyValuePair $kvp */
        foreach ($this as $kvp) {
            if ($kvp->key === $key) return $kvp->value;
        }
        throw new Exception();
    }

    function forEach(callable $f): void
    {
        /** @var KeyValuePair $kvp */
        foreach ($this as $kvp) {
            $f($kvp);
        }
    }

    /**
     * @throws Exception
     */
    function map(callable $f, $keyType = null, $valueType = null): Dictionary
    {
        $new = new Dictionary($keyType ?? $this->keyType, $valueType ?? $this->valueType);
        /** @var KeyValuePair $kvp */
        foreach ($this as $kvp) {
            $keyBuffer = $kvp->key;
            $valueBuffer = $kvp->value;
            $result = null;
            $result = $f($kvp);
            if ($result !== Type::Undefined) $new->add($kvp->key, $kvp->value);
            $kvp->key = $keyBuffer;
            $kvp->value = $valueBuffer;
        }
        return $new;
    }

    /**
     * @throws Exception
     */
    public function current(): mixed
    {
        if ($this->current === null) {
            throw new Exception();
        }
        return $this->current;
    }

    public function next(): void
    {
        $this->current = $this->current->next;
    }

    /**
     * @throws Exception
     */
    public function key(): mixed
    {
        if ($this->current === null) throw new Exception();
        return $this->current->key;
    }

    public function valid(): bool
    {
        return $this->current !== null;
    }

    public function rewind(): void
    {
        if ($this->current === null) $this->current = $this->last;
        while ($this->current && $this->current->before !== null) $this->current = $this->current->before;
    }

    /**
     * @throws Exception
     */
    public function remove(mixed $key): void
    {
        /** @var KeyValuePair $kvp */
        foreach ($this as $kvp) {
            if ($kvp->key !== $key) continue;
            if ($kvp->before) $kvp->before->next = $kvp->next;
            else $this->current = $kvp->next;
            if ($kvp->next) $kvp->next->before = $kvp->before;
            else $this->last = $kvp->before;

            return;
        }
        throw new Exception();
    }

    public function toArray(): array
    {
        $array = [];
        /** @var KeyValuePair $kvp */
        foreach ($this as $kvp) {
            $array[$kvp->key] = $kvp->value;
        }

        return $array;
    }
}

$dic = new Dictionary(Type::String, Type::String);
$dic->add('wow', '3');
$dic->add('1', 'one');
$dic->add('oho', 'noice');

/** @var KeyValuePair $kvp */
$newDic1 = $dic->map(fn($kvp) => $kvp->value .= '_added');

/** @var KeyValuePair $kvp */
$newDic2 = $dic->map(function ($kvp) {
    if (is_numeric($kvp->value)) $kvp->value = (int)$kvp->value;
    else return Type::Undefined;
}, Type::String, Type::Integer);

var_dump($dic->toArray());
var_dump($newDic1->toArray());
var_dump($newDic2->toArray());