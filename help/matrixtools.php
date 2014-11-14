<b>MatrixTools Plugin</b>

<p>The Matrixtools plugin was created to be both a useful tool and to show off the power of the Real Time Pixel Overlay functionality of FPP.  There are two tabs in the main UI of the plugin.  The first allows you to scroll dynamic text messages across a matrix or pixel tree in real-time even while a sequence is running.  The second tab was created to show the power of the Pixel Overlay system and lets you draw on your display with a mouse as if it were a paint brush.</p>

<p>The top line of each tab is the same.  This contains a dropdown list of the Pixel Overlay models defined in FPP.  To work with a specific model, select it from the drop-down list.  Once you have chosen a model, you can control the state of the model.  The model may be in one of 4 states:</p>

<li><b>Disabled</b> - The default state is <b>Disabled</b>.  In this state, the model has no effect and regular sequence data 'shows through' onto the display.</li>
<li><b>Enabled</b> - The <b>Enabled</b> state turns the model On and causes the Pixel Overlay data to override any sequence data for the channels defined by the model.</li>
<li><b>Transparent</b> - The <b>Transparent</b> state is similar to On, but the Pixel Overlay data only overrides sequence data when the Pixel Overlay data is non-zero.  This effectively makes the model's background transparent.  This state should only really be used for non-RGB display elements.  It is included here in the MatrixTools plugin, but is of more use from the command line using the FPP::MemoryMap Perl module to directly drive the Pixel Overlay models.</li>
<li><b>Transparent RGB</b> - The <b>Transparent RGB</b> state is similar to On, but it looks at channels in groups of 3 then determining whether Pixel Overlay data should override sequence data.  If you want to use Transparent mode on a RGB Matrix or Pixel Tree, this is the mode that you want to use instead of regular Transparent mode.  In Transparent RGB mode, if any channel in a RGB triplet is non-zero, then the data for all 3 channels will be copied overtop of the sequence data.  This allows you to override sequence data on a per-pixel basis instead of on a per-channel basis as the regular Transparent mode operates.</li>
<br>
<p>The top line of each tab also includes buttons to clear the Pixel Overlay and to sync the actual display back to the virtual display at the bottom of the screen.</p>

<p>At the bottom of each tab is a graphical color picker and pallette of pre-defined colors to choose from as well as a virtual display of the matrix or pixel tree.  In Text mode, this display can be used to visualize how the text will look on your matrix.  <b>WARNING: This is a very CPU intensive operation on both the Pi and the web browser due to the amount of data being copied around and should only be used for testing.</b> In draw mode, this display is used to allow you to paint on the display with your mouse.  A 'Round Pixels' checkbox allows you to control whether the virtual display uses square or round pixels.</p>
<br>

<b>Text Tool</b>

<p>The Text tab includes various inputs to use to configure the text that you want to display:</p>

<li><b>Text</b> - The text to display</li>
<li><b>Font</b> - The font used to display the text</li>
<li><b>Font Size</b> - The font size used to display the text</li>
<li><b>Position</b> - The position of the text, currently either 'scroll' or 'center'</li>
<li><b>Scroll Direction</b> - The direction to scroll the text when the Position field is set to 'Scroll'</li>
<li><b>Scroll Speed</b> - The speed at which to scroll text when the Position field is set to 'Scroll'</li>
<br>
<p>Once you have selected the desired options and filled in the text you want to display, click the 'Go' button to show the text on your display.  This will only work when the selected model is set to a non-Disabled state.  If you are scrolling text, it may take a short while to scroll depending on the length of the text provided and the scroll speed.  You need to wait for the scrolling to complete and all characters to be off the screen before trying to display another text message.</p>
<br>

<b>Draw Tool</b>

<p>The Draw</p>
<p>The Draw tab includes various inputs to aid in drawing:</p>

<li><b>Block Fill</b> - The Block Fill button will fill the block up with the current selected color</li>
<li><b>Auto-Fill</b> - The Auto-Fill checkbox will cause the tool to automatically fill up the display whenever a new color is chosen.  This is useful for browsing around the color picker to locate a color that looks good on your display.</li>
