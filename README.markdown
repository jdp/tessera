# Tessera

Tessera is an extremely tiny PHP framework modeleted after [web.py](http://webpy.org), [Sinatra](http://sinatrarb.com), and [Juno](http://brianreily.com/project/juno/).
It allows the developer to map URLs directly to class methods.

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
