PGS Software / Restfony Bundle
==============================

[![Latest Stable Version](https://poser.pugx.org/pgs-soft/restfony-bundle/v/stable)](https://packagist.org/packages/pgs-soft/restfony-bundle)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)](https://php.net)
[![License](https://poser.pugx.org/pgs-soft/restfony-bundle/license)](https://packagist.org/packages/pgs-soft/restfony-bundle)
[![Build Status](https://travis-ci.org/PGSSoft/PgsRestfonyBundle.svg?branch=master)](https://travis-ci.org/PGSSoft/PgsRestfonyBundle)
[![Code Coverage](https://scrutinizer-ci.com/g/PGSSoft/PgsRestfonyBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/PGSSoft/PgsRestfonyBundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/PGSSoft/PgsRestfonyBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/PGSSoft/PgsRestfonyBundle/?branch=master)

Bundle to assist with creating classes for Doctrine Entity with aim to speed up creating RESTful APIs.


Installation
------------

Require the bundle with composer:

```bash
    composer require pgs-soft/restfony-bundle
```

Enable the bundle (with dependent bundles) in the kernel:

```php
    <?php
    // app/AppKernel.php
    
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Pgs\RestfonyBundle\PgsRestfonyBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
            // ...
        );
    }
```

Prepare config for Restfony and FOSRestBundle:

```yaml
    # app/config/rest.yml
    
    jms_serializer:
        handlers:
            datetime:
                default_format: 'Y-m-d\TH:i:sP'
        property_naming:
            separator: null
            lower_case: false
    fos_rest:
        param_fetcher_listener: true
        body_listener: true
        format_listener: true
        routing_loader:
            default_format: json
            include_format: false
        serializer:
            serialize_null: true
    pgs_restfony:
        modules:
```

and include it in the main config:

```yaml
    # app/config/config.yml
    
    imports:
    # ...
        - { resource: rest.yml }
    
    # ...
```

Add routing definition, e.g.:

```yaml
    # app/config/routing.yml 
    
    # ...
    
    appbundle_rest:
        resource: "@AppBundle/Resources/config/rest_routing.yml"
        prefix:   /api/
```


Usage
-----

Having an entity run command:

```bash
    bin/console pgs:generate:crud MyEntity
```
    
provide entity shortcut name (e.g. `AppBundle:MyEntity`), decide about adding "write" actions and generation of routing.

New files will be generated:
 - Controller/MyEntityController.php - controller with RESTful actions and ApiDoc
 - Form/Filter/MyEntityFilterType.php - form filter class
 - Form/Type/MyEntityType.php - form type class
 - Manager/MyEntityManager.php - empty class to manage repository
 - Manager/MyEntityManagerInterface.php - interface for manager from above
 - Repository/MyEntityRepository.php - empty repository class
 - Resources/config/serializer/Entity.MyEntity.yml - config for JMS Serializer
 - Tests/MyEntityControllerTest.php - set up of tests for generated actions

And configs will be updated:
 - rest.yml - module for MyEntity will be added
 - rest_routing.yml - entry for MyEntity will be added


API Doc
-------

If needed you can add NelmioApiDocBundle API documentation route, e.g.:

```yaml
    # app/config/routing_dev.yml
    
    # ...
    
    NelmioApiDocBundle:
        resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
        prefix:   /api/doc
```


Authors
-------
 - [Lech Groblewicz](https://github.com/xrogers) <lgroblewicz@pgs-soft.com>
 - Michał Sikora


Contributors
------------
 - [Tomasz Brzeziński](https://github.com/tbrzezinski) <tbrzezinski@pgs-soft.com>
 - [Karol Joński](https://github.com/kjonski) <kjonski@pgs-soft.com>
 - [Krzysztof Maczkowiak](https://github.com/maczkus) <kmaczkowiak@pgs-soft.com>
 - [Grzegorz Mandziak](https://github.com/alimek) <gmandziak@pgs-soft.com>
 - [Kamil Purzyński](https://github.com/kamil-p) <kpurzynski@pgs-soft.com>
 - [Dariusz Rzeźnik](https://github.com/dariusz-rzeznik) <drzeznik@pgs-soft.com>
 - [Jakub Werłos](https://github.com/kubawerlos) <jwerlos@pgs-soft.com>
 - [Krzysztof Wojtas](https://github.com/kwojtas6) <kwojtas@pgs-soft.com>


License
-------

MIT License

Copyright (c) 2016 PGS Software


Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.