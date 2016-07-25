Code based on custom routing
=====

0. Create a new symfony3 project and then copy the files from the repository.
For example the repository has the only controller with one action that can be reached with url
 http://localhost:8000/app/test/test (according to rule: /{bundle_name}/{controller}/{action})

1. Add a routing
```YAML
/app/config/routing.yml
app:
    resource: .
    type: extra
```
2. Add a new service
```YAML
/app/config/services.yml
services:
    app.routing_loader:
        class: AppBundle\Routing\ExtraLoader
        arguments: ["@kernel"]
        tags:
            - { name: routing.loader }
```

3. Extend Loader class
```

```

