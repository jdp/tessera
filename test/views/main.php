<p>Views are automatically created according to the name of the action, as long as the <code>$this->view</code> variable in your action is untouched by your code.</p>
<p>For example, I'm <span class="filename"><?php echo $this->getFilename(); ?></span>.</p>
<p>The Eiffel 65 lyrics are now being passed with the <code>set</code> method.</p>
<p style="background-color: #cccccc; padding: 1em;"><?php echo $lyrics; ?></p>
<p>You can even change which view gets used by changing <code>$this->view->name</code> in the action's method.</p>
<p>Views are also not necessary. If the view's file doesn't exist, or if <code>$this->use_view</code> is set to <code>FALSE</code> in your application, anything output by your actions will be displayed.</p>
