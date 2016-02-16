SiteBuild
=========

SiteBuild is designed to build static websites, built in the same way as you would build a dynamic website.

It uses PHP, and the Plates PHP Template engine, which keeps things simple.

For each site, you create a directory structure like this:

    - menus
    - output
    - pages
    - resources
    - themes

## Installation
    composer global require thomas-redstone/site-build

Add the following to your .bashrc, .zshrc or what ever is required to alter your path variable!
For OSX or Linux, it will be something like this
    export PATH="$PATH:$HOME/.composer/vendor/bin"

The best resource I could find, which explains how to modify the path seems to be this help page from Oracle, for Java,
but it still applies for PHP and composer, so if you're on Windows, or just about any Operating System under the Sun,
this should tell you how to modify your path:

https://java.com/en/download/help/path.xml

## Usage

First of all, you must ensure that there is an 'app.yml' configuration file containing the site name, inside the
 project folder e.g.:

    name: ExampleSite

At present, this is not used, but it must exist, as SiteBuild uses it as confirmation that it is being run in the
correct directory.

Next, run the sitebuild command:

    sitebuild

At this point you should have a populated `output` directory. The directory `output/latest` will always contain
the most recently generated site, and you can test it works locally as follows:

    cd output/latest && php -S localhost:8080

At which point, you should be able to navigate to http://localhost:8080 and see your website.

## Menus
Menus are defined as .yml files, at present there is only a main menu which will be used, but it is planned to support
many menus in the future. For now, the main menu should exist in menus/main.yml, the format being as follows:

    -
      url: "/"
      text: Home
    -
      url: "/about"
      text: About

## Output
This is where the generated website will be placed, at present each day gets a duplicate copy, this behaviour is likely
to change soon, and keeping the websites in version control would be the preferred way to ensure there is always a way
to roll back to a previous version.

## Pages
The site content lives in the pages directory, these should all be .php files, with something along these lines at the
beginning of each file, the template will use the data passed to it in order to populate the title, and the class
provided will be applied to the body element, allowing any page specific styling to be handled. At present the tags
and date fields are not used, but I plan to do something with them soon, and they are part of the reason that the
$config is declared as a variable, and not simply an array declared as the layout function is called.

    <?php
    $config = [
        'tags' => ['Documentation'],
        'date' => '2015-12-11',
        'title' => 'Documentation',
        'class' => 'docs'
    ];
    $this->layout('templates::template', $config);
    ?>

## Resources
Any files placed in the resources directory will be copied directly into the output directory, with any folder structure
preserved, so it's an ideal place to put any images, stylesheets, javascript or other assets.

## Themes
Themes is where the templates themselves are, I generally call my main template 'template', and have a 'menu', which
looks something like this:

    <ul class="nav nav-pills">
        <?php foreach($menu as $menuItem): ?>
            <li class='nav-item'><a class='nav-link' href='<?=$menuItem['url']?>'><?=$menuItem['text']?></a></li>
        <?php endforeach; ?>
    </ul>

# Plans
I started this project around 3 years ago, and for much of that time, I've not done much with it, recently I've began
to modernise it.

A few of the features I hope to add are:
- Tag pages, using tags from the individual pages
- Multiple menu support
- Generated content list pages (like a blog index page)
- Automated pushing of generated sites to Amazon S3
- Create a 'sample project' repository, to make it easier to get started

I'd love to hear any feedback, bug reports, of feature suggestions, so please feel free to raise an issue on the issue
tracker, or email <thomas@redstone.me.uk>.