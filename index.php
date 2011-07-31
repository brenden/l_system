<?php

	/* L System Generator
	   Brenden Kokoszka */
	
	//XHTML Content Type
	header('content-type: application/xhtml+xml; charset=UTF-8');
	
	//Inputs
        $production_string = $_GET['production'];
        $axiom = $_GET['axiom'];
        $iterations = $_GET['iterations'];
        $display_string = $_GET['display'];
        $type = $_GET['type'];

	//Generate rule lookups and generations
	$production_rules = make_lookup($production_string, '->');
	$display_rules = make_lookup($display_string, ':');

	if ($iterations>0) {

		$generations = sub($axiom, $production_rules, $iterations);
	}

	//Change a comma-separated key-value $dictionary_string into a key-value lookup array
	//A given $delineator demarks the key from the value.
	function make_lookup($dictionary_string, $delineator) {
	
		$dictionary_string = preg_split('/\s*,\s*/', $dictionary_string);
	
		foreach ($dictionary_string as $pair) {

			$regexp = "/\s*$delineator\s*/";
			$parts = preg_split($regexp, $pair);
			$dictionary[$parts[0]]=$parts[1];
		}

		return $dictionary;
	}

	//Perform the l-system substitutions
	function sub($axiom, $rules, $iterations) {

		$generations[0] = $axiom;

		for ($i=1; $i<$iterations; $i++) {

			$chars = str_split($generations[$i-1]);

			foreach ($chars as $char) {

				if ($rules[$char]) {

					$generations[$i] = "$generations[$i]$rules[$char]";
				}
				else {
	
			                $generations[$i] = "$generations[$i]$char";
				}
			}
		}

		return $generations;
	}

	//Selects the appropriate way to represent the L-System
	function generate($display_rules, $generations, $type) {

		if ($type=="turtle") {
			
			display_turtle($display_rules, end($generations));
		}
		else if ($type=="blocks") {

			display_blocks($display_rules, $generations);
		}
		else if ($type=="text") {

			display_text($generations);
		}
	}

	//Generate a turtle graphics SVG representation
	function display_turtle($display_rules, $generation) {

		$angle = 90;
		$x = 0;
		$y = 0;
		$min_x = 0;
		$min_y = 0;
		$max_x = 0;
		$max_y = 0;
		$coordinates = array(array(0, 0));
		$chars = str_split($generation);

                foreach ($chars as $char) {

			$instruction = $display_rules[$char];

			if (substr($instruction, -1)=='*') {

				$angle += (int) trim($instruction, '*');
			}
			else {

				//Calculate new x and y values
				$length = (int) $instruction;
				$x = round($x+cos(deg2rad($angle))*$length, 2);
				$y = round($y+sin(deg2rad($angle))*$length, 2);
				array_push($coordinates, array($x, $y));

				//Update minimums & maximums
				$min_x = ($min_x<$x) ? $min_x : $x;
                                $min_y = ($min_y<$y) ? $min_y : $y;
                                $max_x = ($max_x>$x) ? $max_x : $x;
                                $max_y = ($max_y>$y) ? $max_y : $y;
			}
		}

		//Move the coordinates so that 0 is the minimum for both x and y
		for ($i=0; $i<sizeof($coordinates); $i++) {

			$coordinates[$i][0]-=$min_x-5;
                        $coordinates[$i][1]-=$min_y-5;
		}

		?>
			<svg width="<?php echo $max_x-$min_x+10; ?>" height="<?php echo $max_y-$min_y+10; ?>" version="1.1" xmlns="http://www.w3.org/2000/svg">
		<?php
	
		//Plot the coordinates
		for ($i=1; $i<sizeof($coordinates); $i++) {

                	?>
                        	<line 
                                	x1="<?php echo $coordinates[$i-1][0]; ?>"
                                        y1="<?php echo $coordinates[$i-1][1]; ?>" 
                                        x2="<?php echo $coordinates[$i][0]; ?>" 
                                        y2="<?php echo $coordinates[$i][1]; ?>" 
                                        style="stroke:rgb(99,99,99); stroke-width:2"
                                />
                        <?php
		}

		echo "</svg>";
	}

	function display_text($generations) {
	
		echo "<pre>";

		foreach ($generations as $num=>$generation) {

			echo "<strong>$num</strong>: $generation<br />";
		}

		echo "</pre>";
	}

	function display_blocks($display_rules, $generations) {

		$width = 800; //The total width of each display row
		$height = 25; //The height of each row;

		?>
			<svg width="<?php echo $width; ?>" height="<?php echo $height*sizeof($generations); ?>" version="1.1" xmlns="http://www.w3.org/2000/svg">
		<?php

		foreach ($generations as $gen_index=>$generation) {

			$chars = str_split($generation);
			$length = sizeof($chars);

			foreach ($chars as $char_index=>$char) {

				$color = $display_rules[$char];
				?>
					<rect 
						x="<?php echo $char_index*($width/$length); ?>"
						y="<?php echo $gen_index*$height; ?>"
						width="<?php echo $width/$length; ?>"
						height="<?php echo $height; ?>"
						style="<?php echo "fill:$color"; ?>"
					/>
				<?php
			}
		}

		echo "</svg>";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:svg="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:lang="en">

	<head>

		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<title>Lindenmayer System</title>
		<script type="text/javascript" src="jquery.js"></script>
                <script type="text/javascript" src="ui.js"></script>
                <link rel="stylesheet" type="text/css" href="style.css"/>

	</head>

	<body>

		<h1>Lindenmayer System</h1>

		<a id="help-icon" href="#">?</a>

		<div id="information">
			
			<h2>Information</h2>

			<p>
A Lindemayer System (L-System) is a method of recursive string-rewriting. A given grammar defines the way one character can be replaced with others. The result of these rewrites can then be displayed textually or graphed in several different ways.
			</p> 

			<p>
To use this program, define the production rules like this: <code>A->ABA, B->BB</code>. Display rules determine how the image is drawn. The way display rules are interpreted depends on which display type is selected. For turtle graphics, indicate the "draw forward" command with a distance, and a "turn" command with a degree followed by an asterisk. For example: <code>F:10, L:-90*, R:90*</code>. For block graphics, define a color for each symbol: <code>A:black, B:white, C:red, D:blue</code>.
			</p>

			<p>
<strong>Some examples</strong>: <a href="?production=F->FLGRFRGLF%2C+G->GG&amp;axiom=FLGLG&amp;iterations=6&amp;display=F%3A10%2C+G%3A10%2C+L%3A120*%2C+R%3A-120*&amp;type=turtle">Sierpinski Triangle</a>, <a href="?production=A->ABA%2C+B->BBB&amp;axiom=A&amp;iterations=6&amp;display=A%3Ablack%2C+B%3Awhite&amp;type=blocks">Cantor's Dust</a>, <a href="?production=F->FLFRFRFLF&amp;axiom=F&amp;iterations=3&amp;display=F%3A15%2C+L%3A-90*%2C+R%3A90*&amp;type=turtle">Koch Curve</a>, <a href="?production=F->FLFR&amp;axiom=F&amp;iterations=10&amp;display=F%3A15%2C+L%3A-90*%2C+R%3A90*&amp;type=turtle">Interesting Curve #1</a>, <a href="?production=F->RL%2C+L->FR%2C+R->FL&amp;axiom=F&amp;iterations=10&amp;display=F%3A10%2C+L%3A-90*%2C+R%3A90*&amp;type=turtle">Interesting Curve #2</a>.
			</p>
		</div>

		<div id="options">

			<h2>Rules</h2>

			<form action="lsystem.php" method="get">

				<ul id="system" class="input">

					<li>
						<strong>Production Rules</strong>
						<br />
					        <input type="text" name="production" size="30" value="<?php echo $production_string; ?>" />
					</li>

					<li>
						<strong>Axiom</strong>
						<br />
						<input type="text" name="axiom" size="10" value="<?php echo $axiom; ?>" />
					</li>

					<li>
						<strong>Iterations</strong>
						<br />
						<input type="text" name="iterations" size="10" value="<?php echo $iterations; ?>" />
					</li>

				</ul>
	
				<ul id="display" class="input">
		
		                	<li>
		        	                <strong>Display Rules</strong>
			                        <br />
			                        <input type="text" name="display" size="30" value="<?php echo $display_string; ?>" />
		                	</li>

		        	        <li>
			                        <strong>Display Type</strong>
			                        <br />
						<input type="radio" name="type" value="turtle" <?php echo ($type=="turtle" || !$type) ? 'checked="true"' : ''; ?> /> 
						<label class="display-type">Turtle</label>
						<input type="radio" name="type" value="blocks" <?php echo ($type=="blocks") ? 'checked="true"' : ''; ?> />
						<label class="display-type">Block</label>
		        	                <input type="radio" name="type" value="text" <?php echo ($type=="text") ? 'checked="true"' : ''; ?> />
						<label class="display-type">Text</label>
			                </li>

				</ul>

				<input type="submit" class="button" value="Render"/>
				<button class="button" id="clear">Clear</button>

			</form>
		</div>

		<div id="result">

			<h2>Output</h2>
	
			<?php generate($display_rules, $generations, $type); ?>

		</div>

	</body>

</html>
