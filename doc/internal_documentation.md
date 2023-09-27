# Documentation interne

Created by: Dimitri RUTZ
Created time: September 13, 2023 11:08 AM
Tags: Engineering, Product

# Structure du framework


> ### ⚠️ Note
> Ceci constitue la structure interne du framework et non celle d’une application utilisant le framework


- **src/**
    - framework/ (**MVC**)
        - Http/ (**MVC\Http**)
            
            Toutes les classes relatives à la gestion des requêtes et des réponse HTTP. Ainsi que le routing des requêtes
            
        - Form/ (**MVC\Form**)
            
            Contient toute les classes relatives à la construction de formulaire (impliquant la traduction d’un résultat d’une requête POST en un objet d’entité, génération de vues de formulaire, hydratation des formulaires d’après une entité etc)
            
        - View/ (**MVC\View**)
            
            Contient toute les classes relatives à la génération de vues
            
    - orm/ **(ORM)**
        - Mapping/ **(ORM\Mapping)**
            
            Contient les attributs permettant à l’entity builder de transformer une classe PHP en une table (SQL dans notre cas)
            
        - DBAL/ **(ORM\DBAL)**
            
            Contient les abstractions des composants d’une base de donnée
            
        - Driver/ **(ORM\Driver)**
            
            Contient un adaptateur aux base de donnée pour les différents moteur de base de donnée. Un driver sera fournis par défaut, soit celui de MySQL.
            
- **tests/**
    - Http
        
        Tous les tests relatifs à la gestion des requêtes et des réponse HTTP. Ainsi que le routing des requêtes
        
    - Form
        
        Contient tous les tests relatifs à la construction de formulaire (impliquant la traduction d’un résultat d’une requête POST en un objet d’entité, génération de vues de formulaire, hydratation des formulaires d’après une entité etc)
        
    - View
        
        Contient tous les tests relatifs à la génération de vues
        
- **vendor/**
- composer.json
    
    Contient les différentes dépendance de développement et principale du framework ainsi que les information du framework comme la version les auteurs et le chemin des dossiers à autoload avec leur namespace de premier niveaux
    
    [ **MVC** → src/framework/, **ORM** → src/orm/ ]
    
- .php-cs-fixer.php
    
    Contient les règles mises en formes notamment celles de PSR12
    

## PHP CS Fixer

---

Outils permettant la définition de règles afin d’aider l’éditeur à respecter certaines nomenclature définie dans le fichier `.php-cs-fixer.php`.

Toutes les informations sur son utilisation avec PhpStorm sont disponibles dans la [documentation de JetBrains](https://www.jetbrains.com/help/phpstorm/using-php-cs-fixer.html#prerequisites) CS Fixer étant nativement supporté par PhpStorm.

## PHPUnit

---

Outils de tests unitaires. Utilisation, créer une class de test dans le dossier relatif au domaine de votre test dans le dossier `tests/` .

Documentation de [PHPUnit](https://phpunit.de/getting-started/phpunit-10.html)

# Routing

> ### ⚠️ Note
> Ceci constitue la structure interne du routing du framework et non celle d’un routing utilisant le framework


## Lexical

| parameter/params | /user/`[dimitri_rutz]`/edit                                                              |
|------------------|------------------------------------------------------------------------------------------|
| Query            | /user?`order=ASC`                                                                        |
| Data             | Data passed with Post Method                                                             |
| Method           | Http Method == Http verb [(MZN)](https://developer.mozilla.org/fr/docs/Web/HTTP/Methods) |
|                  |                                                                                          |

## Composants

### Kernel

Le kernel est le noyau de l’application soit le point d’entrée de celle-ci. L’initialisation du Kernel définit les outils principeaux de l’application. Son rôle est notament de lire les variables d'environnments en utilisant le composant [symfony/dotenv](https://symfony.com/components/Dotenv).
