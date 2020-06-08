# Managers
Managers by nature are mostly `abstract` classes that are used to inherited from but it could be also normal classes.

Managers should be used to **encapsulate** common methods between same classes like [RepositoryManager](#repository-manager) for instance, it's used to implement some common methods between all [repositories](#repositories) and also add some `abstract` method for the child classes.

- [Managers](#managers)
- [Available Managers](#available-managers)

# Available Managers
- [MYSQL Model Manager](./model-manager)
- [MongoDB Model Manager](./mongodb-model-manager)
- [Repository Manager](./repository-manager)
- [MongoDB Repository Manager](./mongodb-repository-manager)
- [Api Controller](./api-controller)
- [Admin Api Controller](./admin-api-controller)
