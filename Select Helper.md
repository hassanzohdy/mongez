# Select Helper

This class is mainly used in the [RepositoryManager](./repository-manager) to handle the select option.

Class path: `HZ\Illuminate\Mongez\Helpers\Repository\Select`

- [Select Helper](#select-helper)
- [Example](#example)
- [Class Methods](#class-methods)
  - [Constructor](#constructor)
  - [add](#add)
  - [remove](#remove)
  - [replace](#replace)
  - [has](#has)
  - [list](#list)
  - [isEmpty](#isempty)
  - [isNotEmpty](#isnotempty)


# Example

For a real usage example for the class, please pay a visit to [RepositoryManager](./repository-manager).

# Class Methods
Here is a full list of methods that the select class provides

## Constructor

`Select::__construct(array $selectList)`

Pass the select list array to the constructor

## add

`add(...$columns): self`

Add one ore more column to the select list

## remove

`remove(mixed $column): self`

Remove the given column **value** from the list

## replace

`replace($oldColumn, mixed $column): self`

Replaces the given oldColumn **value** with one ore more column


## has

`has(string $column): bool`

Determine if the given column exists in select list

## list

`list(): array`

Return full list of select options in array.

## isEmpty

`isEmpty(): bool`

Determine if the select list is empty.
  
## isNotEmpty

`isNotEmpty(): bool`

Determine if the select list is **not** empty. 