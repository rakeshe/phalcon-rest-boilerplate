# Phalcon REST Boilerplate

[![Phalcon REST Boilerplate](https://phalconist.com/redound/phalcon-rest-boilerplate/default.svg)][0]

Phalcon REST Boilerplate to use with [Phalcon REST library][1].

## Learn More

- [Quick Start][2]
- [Documentation][3]

## Contributing

Please file issues under GitHub, or submit a pull request if you'd like to directly contribute.

## License

Phalcon REST is licensed under the MIT license. See [License File](LICENSE.md) for more information.

[0]: https://phalconist.com/redound/phalcon-rest-boilerplate
[1]: https://github.com/redound/phalcon-rest
[2]: http://phalcon-rest.redound.org/quick_start.html
[3]: http://phalcon-rest.redound.org


## Scaffolding 
You can quickly build all necessary objects required based your custom api definition config without writing single line
of code.

### Prepare api config file (json)

Create a api config.json file describing your api and save it somewhere in your pc. Note its location path.

api config guide:
    {
      "*resource": {
        "*{name}": {
          "scope": "{crud|factory}",    // default: factory
          "model": "{model class}",     // default: {name}
          "transformer": "{data transformer class}",    // default: {name}Transformer
          "handler": "{controller class}",              // default: {name}Controller
          "*deny": "{one or more(csv) Acl Roles:UNAUTHORIZED|AUTHORIZED|MANAGER|ADMINISTRATOR}",
          "endpoints": {
            "all|find|create|update|remove": {
              "allow": "{one or more Acl Roles}",
              "deny": "{one or more Acl Roles}",
              "description": "{end point description for documentation}"
            }
          }
        }
      }
    }
* denotes mandatory, rest are mutually exclusive: each resource or collection item must have at least one element defined.
Installer tool will use default values if elements are not defined.

#### Examples
{
    "resource": {
        member": {
          "scope": "crud",
          "deny": "UNAUTHORIZED"
        }
    }
}
will build Controllers/MemberController, Model/Member, Resources/MemberResource, Transformers/MemberTransformer, 
with endpoints for GET:members/, GET:members/{member PK id}, POST:members/, PUT:members/ and DELETE:members/ accessible 
to logged in user (ie with token)


