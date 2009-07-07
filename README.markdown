# Tessera

Tessera is an extremely tiny PHP framework modeled after [web.py](http://webpy.org), [Sinatra](http://sinatrarb.com), and [Juno](http://brianreily.com/project/juno/).
It allows the developer to map URIs to class methods while keeping their code DRY, and separating application logic and presentation.
Its feature set is small and strong: routes with named params, view and layout logic.

## Why?

Sometimes, I just have to work in PHP. I would love to use a new and different framework for every job, but that's just not possible.
Tessera was created because I looked for a "minimal" PHP framework, but none of the frameworks I found fit my definition, and I think there are people out there who are looking for the same thing.
Sure, some of the frameworks I found were small, like [wephp](http://code.google.com/p/wephp/) and [tkself](http://tkself.org/), but they just weren't what I was looking for.
Tessera gives you exactly what it says it will, and then gets out of your way.

## Your first application, or: never looking back

Creating a working application in Tessera is more than easy.
All that needs to happen is a superclassing of the Tessera class.
Here's a fully functional application:

    <?php
    require 'tessera.php';
    
    class BasicApp extends Tessera {
        function index() {
            echo "Deep down, deep down, dadi dadu dadu dadi dada";
        }
    }
    
    $basic = new BasicApp(array(
        '/' => 'index'
    ));

That's it. Save that as **basic.php** in your document root, and visit **yourdomain.com/basic.php**.
That's an entire Tessera application that responds to a request to /, which gets mapped to the `index` method of BasicApp.
If everything was done properly, you should be greeted with Eiffel 65 lyrics.

### Getting nutty

If you want to explore Tessera more, check out the [project wiki](http://wiki.github.com/jdp/tessera).

## About

&copy; 2009 [Justin Poliey](http://justinpoliey.com)
