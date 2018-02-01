<style>
canvas.matrix {
	height: 371px;
	width: 741px;
}

.matrix-tool-top-panel {
	padding-bottom: 0px !important;
}

.matrix-tool-middle-panel {
	padding-bottom: 0px !important;
	padding-top: 0px !important;
}

.matrix-tool-bottom-panel {
	padding-top: 0px !important;
}

.red {
	background: #ff0000;
}

.green {
	background: #00ff00;
}

.blue {
	background: #0000ff;
}

.yellow {
	background: #ffff00;
}

.orange {
	background: #ff8800;
}

.white {
	background: #ffffff;
}

.black {
	background: #000000;
}

.colorButton {
	-moz-transition: border-color 250ms ease-in-out 0s;
	background-clip: padding-box;
	border: 2px solid rgba(0, 0, 0, 0.25);
	border-radius: 50% 50% 50% 50%;
	cursor: pointer;
	display: inline-block;
	height: 20px;
	margin: 1px 2px;
	width: 20px;
}

</style>

<script type="text/javascript">
var wsIsOpen = 0;
var ws;
var blockList = {};
var blockData = [];
var blockName = "Matrix1";
var dataIsPending = 0;
var pendingData;
var penWidth = 1;

if ( ! window.console ) console = { log: function(){} };

function WSGetBlockData(data)
{
	var dbws = new WebSocket("ws://<? echo $_SERVER['HTTP_HOST']; ?>:32321/echo");
	dbws.onopen = function()
	{
		dbws.send(JSON.stringify(data));
	}
	dbws.onmessage = function(evt)
	{
		blockData = JSON.parse(evt.data).Result;
		setColorsFromData();
	}
}

function SyncBackDisplay() {
	WSGetBlockData( { Command: "GetBlockData", BlockName: blockName } );
}

function SendWSCommand(data)
{
	if (!wsIsOpen)
	{
		dataIsPending = 1;
		pendingData = data;

		ws = new WebSocket("ws://<? echo $_SERVER['HTTP_HOST']; ?>:32321/echo");
		ws.onopen = function()
		{
			wsIsOpen = 1;
			if (dataIsPending)
			{
				dataIsPending = 0;
				ws.send(JSON.stringify(pendingData));
			}
		}
		ws.onmessage = function(evt)
		{
			var data = JSON.parse(evt.data);
			if (data.Command == "GetBlockList") {
				blockList = JSON.parse(evt.data).Result;
				ProcessBlockListResponse();
			} else if (data.Command == "GetBlockData") {
				var parsedData = JSON.parse(evt.data)
				blockData = parsedData.Result;
				setColorsFromData();
				if (!parsedData.Locked)
					StopBlockDataTimer();
			} else if (data.Command == "GetFontList") {
				ProcessFontListResponse(JSON.parse(evt.data).Result);
			}
		},
     	ws.onclose = function()
		{ 
		 	wsIsOpen = 0;
		};
	} else {
		ws.send(JSON.stringify(data));
	}
}

</script>

<script>
	var currentColor = '#ff0000';
	var cellColors = {};

	function testModeOn() {
		SendWSCommand({ Command: "SetTestMode", State: 1 });
	}

	function testModeOff() {
		SendWSCommand({ Command: "SetTestMode", State: 0 });
	}

	function blockState() {
		var state = $('#blockOnOffSwitch').val();

		SendWSCommand({ Command: "SetBlockState", BlockName: blockName, State: state });
	}

	function autoFillChanged() {
		if ($('#AutoFill').is(':checked'))
			FillMatrix();
		else
			ClearMatrix();
	}

	function refreshMatrix() {
		$('#mmcanvas').drawLayers();
	}

	function setColor(color) {
		if (color.substring(0,1) != '#')
			color = '#' + color;

		currentColor = color;
		$('#colpicker').colpickSetColor(color);

		if ($('#AutoFill').is(':checked'))
			FillMatrix();
	}

	function setColorsFromData() {
		cellColors = {};
		var width = blockList[blockName].width;
		for (var p = 0; p < blockData.length; p += 3)
		{
			var x = p / 3 % width;
			var y = parseInt(p / 3 / width);
			var key = x + "," + y;
			cellColors[key] = '#' + $.colpick.rgbToHex({ r: blockData[p], g: blockData[p+1], b: blockData[p+2]});
		}

		refreshMatrix();
	}

	function hexToRgb(hex) {
		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		return result ? {
			r: parseInt(result[1], 16),
			g: parseInt(result[2], 16),
			b: parseInt(result[3], 16)
		} : null;
	}

	function PixelHex(x, y, hex) {
		var rgb = hexToRgb(hex);
		return { Command: 'SetBlockPixel', BlockName: blockName, X: x, Y: y, RGB: [ rgb.r, rgb.g,rgb.b ] };
	}

	function PixelRGB(x, y, r, g, b) {
		return { Command: 'SetBlockPixel', BlockName: blockName, X: x, Y: y, RGB: [ r, g, b ] };
	}

	function ClearMatrix() {
		SendWSCommand( { Command: "ClearBlock", BlockName: blockName } );
		cellColors = {};
		refreshMatrix();
	}

	function selectBlock(name) {
		blockName = name;
		GetBlockData();
		InitCanvas();

		$('#blockOnOffSwitch').val(blockList[blockName].isActive);
	}

	function GetBlockData() {
		SendWSCommand( { Command: "GetBlockData", BlockName: blockName } );
	}

	var blockDataTimer = null;
	function StartBlockDataTimer() {
		blockDataTimer = setInterval(function(){GetBlockData()}, 100);
	}

	function StopBlockDataTimer() {
		if (blockDataTimer != null) {
			clearInterval(blockDataTimer);
		}
	}

	function GetFontList() {
		SendWSCommand( { Command: "GetFontList" } );
	}

	function ProcessFontListResponse(list) {
		$('#fontList option').remove();
		for (var i = 0; i < list.length; i++) {
			var key = list[i];
			var text = key.replace(/[^-a-zA-Z0-9]/g, '');
			if (key == text)
			{
				if (key == "fixed")
					$('#fontList').append("<option value='" + key + "' selected>" + text + "</option>");
				else
					$('#fontList').append("<option value='" + key + "'>" + text + "</option>");
			}
		}
	}

	function GetBlockList() {
		SendWSCommand( { Command: "GetBlockList" } );
	}

	function ProcessBlockListResponse() {
		GetFontList();
		$('#blockList option').remove();
		blockName = "";
		var sortedNames = Object.keys(blockList);
		sortedNames.sort();
		for (var i = 0; i < sortedNames.length; i++) {
			var key = sortedNames[i];
			if (blockName == "")
				blockName = key;
			if (blockList[key].orientation == 'V')
			{
				blockList[key].height = blockList[key].channelCount / blockList[key].strandsPerString / blockList[key].stringCount / 3;
				blockList[key].width = blockList[key].channelCount / 3 / blockList[key].height;
			}
			else
			{
				blockList[key].width = blockList[key].channelCount / blockList[key].strandsPerString / blockList[key].stringCount / 3;
				blockList[key].height = blockList[key].channelCount / 3 / blockList[key].width;
			}
			$('#blockList').append("<option value='" + key + "'>" + key + " (" + blockList[key].width + "x" + blockList[key].height + ")</option>");
		}

		selectBlock(blockName);
	}

	function FillMatrix() {
		var rgb = hexToRgb(currentColor);
		SendWSCommand( { Command: "SetBlockColor", BlockName: blockName,
			RGB: [ rgb.r, rgb.g, rgb.b ] } );
		if (currentColor == "#000000") {
			cellColors = {};
		} else {
			for (var x = 0; x < blockList[blockName].width; x++) {
				for (var y = 0; y < blockList[blockName].height; y++) {
					key = x + "," + y;
					cellColors[key] = currentColor;
				}
			}
		}
		refreshMatrix();
	}

	function ColorPixel(x, y) {
		if ((x >= blockList[blockName].width) ||
			(y >= blockList[blockName].height) ||
			(x < 0) ||
			(y < 0))
			return;

		// FIXME, enhance to add pen width support (1, 3, 5, 7, 9)
		var key = x + "," + y;
		if (currentColor == "")
			currentColor = '#000000';
				
		if (currentColor == '#000000')
			delete cellColors[key];
		else
			cellColors[key] = currentColor;

		refreshMatrix();

		var rgb = hexToRgb(currentColor);

		var data = { Command: 'SetBlockPixel', BlockName: blockName, X: x, Y: y, RGB: [ rgb.r, rgb.g, rgb.b ] };
		SendWSCommand(data);
	}

	function PlaceText() {
//		ClearMatrix();
		var msg = $('#inputText').val();
		var data = {
			Command: 'TextMessage',
			BlockName: blockName,
			Message: msg,
			Color: currentColor,
			Fill: '#000000',
			Font: $('#fontList').val(),
			FontSize: $('#fontSize').val(),
			Position: $('#textPosition').val(),
			Direction: $('#scrollDirection').val(),
			PixelsPerSecond: $('#scrollSpeed').val(),
			};
		SendWSCommand(data);

		if ($('#ShowTextEffect').is(':checked'))
			StartBlockDataTimer();
	}

	var canvasWidth = 740;
	var canvasHeight = 370;
	var cellsize = 10;
	var mouseDown = 0;
	var mouseDownX= 0;
	var mouseDownY= 0;

	function InitCanvas() {
		if ((blockList[blockName].width > 74) || (blockList[blockName].height > 37))
			cellsize = 5;
cellsize = 5;

xsize = parseInt(740 / blockList[blockName].width);
ysize = parseInt(370 / blockList[blockName].height);
if (xsize < ysize)
	cellsize = xsize;
else
	cellsize = ysize;
if (cellsize > 20)
	cellsize = 20;

var halfCellSize = Math.floor(cellsize / 2);
var quarterCellSize = Math.floor(halfCellSize / 2);

		canvasWidth = blockList[blockName].width * cellsize;
		canvasHeight = blockList[blockName].height * cellsize;

		$('#mmcanvas').removeLayers();
		$('#mmcanvas').clearCanvas();

		// Draw the Black background
		$('#mmcanvas').drawRect({
			fromCenter: false,
			layer: true,
			fillStyle: '#000',
			x: 0,
			y: 0,
			width: canvasWidth,
			height: canvasHeight,
			mousedown: function(layer) {
				var pixelX = Math.floor(layer.eventX / cellsize);
				var pixelY = Math.floor(layer.eventY / cellsize);
				mouseDown = 1;
				mouseDownX = pixelX;
				mouseDownY = pixelY;
			},
			mouseup: function(layer) {
				var pixelX = Math.floor(layer.eventX / cellsize);
				var pixelY = Math.floor(layer.eventY / cellsize);
				mouseDown = 0;
				ColorPixel(pixelX, pixelY);
			},
			mousemove: function(layer) {
				if (mouseDown)
				{
					var pixelX = Math.floor(layer.eventX / cellsize);
					var pixelY = Math.floor(layer.eventY / cellsize);

					ColorPixel(pixelX, pixelY);
				}
			},
		});

		// Draw the grid layer
		$('#mmcanvas').draw({
			layer: true,
			fn: function(ctx) {

				if (pluginSettings['ShowGrid'] == "1") {
					for (var x = 0; x <= canvasWidth; x += cellsize) {
						ctx.beginPath();
						ctx.strokeStyle = '#555';
						ctx.lineWidth = 2;
						ctx.moveTo(x, 0);
						ctx.lineTo(x, canvasHeight);
						ctx.stroke();
					}

					for (var y = 0; y <= canvasHeight; y += cellsize) {
						ctx.beginPath();
						ctx.strokeStyle = '#555';
						ctx.lineWidth = 2;
						ctx.moveTo(0, y);
						ctx.lineTo(canvasWidth, y);
						ctx.stroke();
					}
				}
			}
		});

		// Draw the Pixel layer
		$('#mmcanvas').draw({
			layer: true,
			fn: function(ctx) {
				var keys = Object.keys(cellColors);
				for (var i in keys) {
					var key = keys[i];
					if (cellColors[key] != '#000000')
					{
						var coordinates = key.split(',');
						var x = parseInt(coordinates[0]);
						var y = parseInt(coordinates[1]);
						x = x * cellsize + 1;
						y = y * cellsize + 1;

						ctx.beginPath();
						if (pluginSettings['ShowRoundPixels'] == "1") {
							ctx.arc(x + halfCellSize, y + halfCellSize, quarterCellSize, 0, 2 * Math.PI, false);
						} else {
							ctx.rect(x, y, cellsize - 2, cellsize - 2);
						}
						ctx.fillStyle = cellColors[key];
						ctx.fill();
						ctx.lineWidth = 1;
						ctx.strokeStyle = cellColors[key];
						ctx.stroke();
					}
				}
			}
		});

	}
</script>


<div class='fppTabs'>
	<div class='title'>Matrix Tools</div>
	<div id="matrixTabs">
		<ul>
			<li><a href="#tab-mmtext">Text</a></li>
			<li><a href="#tab-mmdraw">Draw</a></li>
<!--
			<li><a href="#tab-mmimage">Image</a></li>
-->
		</ul>

		<div id= "divSelect" class='ui-tabs-panel matrix-tool-top-panel'>
			<select id='blockList' onChange='selectBlock(this.value);'></select> - State:
			<select id='blockOnOffSwitch' onChange='blockState()'>
				<option value='0'>Disabled</option>
				<option value='1'>Enabled</option>
				<option value='2'>Transparent</option>
				<option value='3'>Transparent RGB</option>
			</select>
			<input type='button' value='Clear' onClick='ClearMatrix();' class='buttons'>
			<input type='button' value='Sync Back' onClick='SyncBackDisplay();' class='buttons'>
		</div>

		<div id="tab-mmtext" class='matrix-tool-middle-panel'>
			<div id="divText">
				<table border=0><tr><td valign='top'>
					<table border=0>
					<tr><td>Text:</td><td colspan=4><input type='text' maxlength='120' size='55' id='inputText'>&nbsp;<input type='button' value='Go' onClick='PlaceText();'></td></tr>
					<tr><td>Font :</td>
						<td><select id='fontList'>
							</select></td>
						<td width='30px'></td>
						<td>Scroll&nbsp;Direction:</td>
						<td><select id='scrollDirection'>
							<option value='R2L' selected>Right To Left</option>
							<option value='L2R'>Left To Right</option>
							<option value='B2T'>Bottom To Top</option>
							<option value='T2B'>Top To Bottom</option>
							</select>
							</td>
						</tr>
					<tr><td>Font&nbsp;Size:</td>
						<td><select id='fontSize'>
							<option value='5'>5</option>
							<option value='6'>6</option>
							<option value='7'>7</option>
							<option value='8'>8</option>
							<option value='9'>9</option>
							<option value='10' selected>10</option>
							<option value='12'>12</option>
							<option value='14'>14</option>
							<option value='16'>16</option>
							<option value='18'>18</option>
							<option value='20'>20</option>
							<option value='22'>22</option>
							<option value='24'>24</option>
							<option value='26'>26</option>
							<option value='28'>28</option>
							<option value='30'>30</option>
							<option value='32'>32</option>
							<option value='34'>34</option>
							<option value='36'>36</option>
							<option value='38'>38</option>
							<option value='40'>40</option>
							<option value='42'>42</option>
							<option value='44'>44</option>
							<option value='46'>46</option>
							<option value='48'>48</option>
							<option value='50'>50</option>
							<option value='52'>52</option>
							<option value='54'>54</option>
							<option value='56'>56</option>
							<option value='58'>58</option>
							<option value='60'>60</option>
							</select>
							</td>
						<td width='30px'></td>
						<td>Scroll&nbsp;Speed (pixels per second):</td>
						<td><select id='scrollSpeed'>
							<option value='1'>1</option>
							<option value='2'>2</option>
							<option value='3'>3</option>
							<option value='4'>4</option>
							<option value='5'>5</option>
							<option value='6'>6</option>
							<option value='7'>7</option>
							<option value='8'>8</option>
							<option value='9'>9</option>
							<option value='10' selected>10</option>
							<option value='11'>11</option>
							<option value='12'>12</option>
							<option value='13'>13</option>
							<option value='14'>14</option>
							<option value='15'>15</option>
							<option value='16'>16</option>
							<option value='17'>17</option>
							<option value='18'>18</option>
							<option value='19'>19</option>
							<option value='20'>20</option>
							<option value='25'>25</option>
							<option value='30'>30</option>
							<option value='35'>35</option>
							<option value='40'>40</option>
							<option value='45'>45</option>
							<option value='50'>50</option>
							<option value='55'>55</option>
							<option value='60'>60</option>
							<option value='65'>65</option>
							<option value='70'>70</option>
							<option value='75'>75</option>
							<option value='80'>80</option>
							<option value='85'>85</option>
							<option value='90'>90</option>
							<option value='95'>95</option>
							<option value='100'>100</option>
							</select>
							</td>
						</tr>
					<tr><td>Position:</td>
						<td><select id='textPosition'>
							<option value='center'>Center</option>
							<option value='scroll' selected>Scroll</option>
							</select></td>
						</tr>
					</table>

				</td><td width='30'>&nbsp;</td><td valign='top'>
				<div id="colpicker2"></div>
				</td></tr></table>
			</div>
		</div>

		<div id="tab-mmdraw" class='matrix-tool-middle-panel'>
			<div id= "divDraw">
					<table border=0><tr><td valign='top'>
						<table border=0>
						<tr><td>Block Fill:</td>
							<td><input type='button' value='Fill' onClick='FillMatrix();' class='buttons'></td>
							</tr>
						<tr><td>Auto-Fill: <? PrintSettingCheckbox("Auto Fill Block", "AutoFill", 0, 0, "1", "0", "fpp-matrixtools", "autoFillChanged"); ?></td>
						</table>
					</table>
			</div>
		</div>

<!--
		<div id="tab-mmimage" class='matrix-tool-middle-panel'>
			<div id= "divImage">
				<fieldset class="fs">
					<legend> Image Tools </legend>
					<select class='blockList'></select> : Off <input type='button' value='On'><br>
					Select Image from Upload Dir<br>
					Scale or crop?<br>
					Crop/Position widget<br>
					<input type='button' value='Make It So'>
				</fieldset>
			</div>
		</div>
-->

		<div id= "divCanvas" class='ui-tabs-panel matrix-tool-bottom-panel'>
			<table border=0><tr><td valign='top'>
			<div id="colpicker"></div>
			</td><td width='30px'></td><td valign='top'>
			<div>
				<table border=0>
					<tr><td valign='top'>Pallette:</td>
						<td><div class='colorButton red' onClick='setColor("#ff0000");'></div>
							<div class='colorButton green' onClick='setColor("#00ff00");'></div>
							<div class='colorButton blue' onClick='setColor("#0000ff");'></div>
						</td>
					</tr>
					<tr><td></td>
						<td><div class='colorButton white' onClick='setColor("#ffffff");'></div>
							<div class='colorButton black' onClick='setColor("#000000");'></div>
						</td>
					</tr>
				</table>
			</div>
			</td></tr></table>
			<br>
			<table border=0>
				<tr><td>Matrix</td>
					<td width='40px'>&nbsp;</td>
					<td>Show Text: <? PrintSettingCheckbox("Show Text Effect", "ShowTextEffect", 0, 0, "1", "0", "fpp-matrixtools"); ?></td>
					<td width='40px'>&nbsp;</td>
					<td>Round Pixels: <? PrintSettingCheckbox("Show Round Pixels", "ShowRoundPixels", 0, 0, "1", "0", "fpp-matrixtools", "refreshMatrix"); ?></td>
					<td width='40px'>&nbsp;</td>
					<td>Show Grid: <? PrintSettingCheckbox("Show Grid", "ShowGrid", 0, 0, "1", "0", "fpp-matrixtools", "refreshMatrix"); ?></td>
				</tr>
			</table>
			<center>
				<canvas id='mmcanvas' class='matrix' height='371' width='741'></canvas>
			</center>
		</div>

	</div>
</div>

<div id='log'></div>

<script>

	$("#matrixTabs").tabs({active: 0, cache: true, spinner: "", fx: { opacity: 'toggle', height: 'toggle' } });

	$('#colpicker').colpick({
		flat: true,
		layout: 'rgbhex',
		color: '#ff0000',
		submit: false,
		onChange: function(hsb,hex,rgb,el,bySetColor) {
			if (!bySetColor)
				setColor('#'+hex);
		}
	});

	GetBlockList();

</script>

