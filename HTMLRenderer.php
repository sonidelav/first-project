<?php
/*
 * Render HTML Code - Static Class
 */
class FTC {
    const CACHE_PATH = '/cache/ftc';
    
    public static $COLORS = array(
        'RED'=>'#ff0000',
        'BLACK'=>'#000000',
        'BLUE'=>'#0000ff',
        'YELLOW'=>'#ffff00',
        'BROWN'=>'#a52a2a',
        'PINK'=>'#ffc0cb',
        'PURPLE'=>'#800080',
        'GRAY'=>'#808080',
        'WHITE'=>'#ffffff',
        'ORANGE'=>'#ffa500',
        'GREEN'=>'#008000',
        'AQUA'=>'#00ffff',
    );
    
    /**
     * Generate Color Palete HTML Code
     */
    public static function GenerateColorPalete(){
        
        echo '<div id="color_palete">';
        
            foreach(self::$COLORS as $name => $hex){
                
                $style = "background:$hex;";
                
                echo '<div class="color_box" title="'.$name.'" style="'.$style.'"></div>';
                
            }
        
        echo '</div>';
    }
    
    /**
     * Convert HEX Color Format to RGB Color Format
     * @param string $hex_str
     * @return array RGB = array('r' => int, 'g' => int, 'b' => int)
     */
    public static function hex2rgb($hex_str){
        $hex_str = str_replace("#", "", $hex_str);
        
        if(strlen($hex_str) == 3){
            $R = hexdec(substr($hex_str,0,1).substr($hex_str,0,1));
            $G = hexdec(substr($hex_str,1,1).substr($hex_str,1,1));
            $B = hexdec(substr($hex_str,2,1).substr($hex_str,2,1));
        } else {
            $R = hexdec(substr($hex_str, 0, 2));
            $G = hexdec(substr($hex_str, 2, 2));
            $B = hexdec(substr($hex_str, 4, 2));
        }
        
        return array('r'=>$R,'g'=>$G,'b'=>$B);
    }
    
    /**
     * Convert RGB Color Format to HEX Color Format
     * @param array $rgb_array array[R, G, B];
     * @return string HEX String with # symbol
     */
    public static function rgb2hex($rgb_array){
        $hex = "#";
        $hex .= str_pad(dechex($rgb_array[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb_array[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb_array[2]), 2, "0", STR_PAD_LEFT);
        return $hex;
    }
    
    /**
     * Check if pixel (R,G,B) data is correct with color name
     * @param type $color_name
     * @param type $pixel
     */
    public static function isCorrectColor($color_name, $pixel, $tolerance=4)
    {
        $color_name = strtoupper($color_name);
        if(array_key_exists($color_name, self::$COLORS))
        {
            $returnBool = array();
            
            // get color_name pixel data (R,G,B);
            $correctData = self::hex2rgb(self::$COLORS[$color_name]);
            
            // get tol range of pixel (min, max);
            $range = self::getToleranceRange($pixel, $tolerance);
            
            // compare and return
            foreach($correctData as $channel => $value)
            {
                // Check if Correct Channel (RGB) value is in Tolerance Range of pixel data.
                if($value >= $range[$channel]['min'] && $value <= $range[$channel]['max'])
                {
                    $returnBool[$channel] = true;
                } else {
                    $returnBool[$channel] = false;
                }
            }
            
            return ($returnBool['r'] && $returnBool['g'] && $returnBool['b']);
        } 
        else throw new Exception('Color: '.$color_name.' is not exist in pre-defined color list');
    }
    
    /**
     * Get Tolerance Range for a Pixel data (R,G,B) per Channel.
     * @param array $pixel
     * @param integer $tolerance must be divided by 2
     * @return array Range[channel] = array('min' => int, 'max' => int)
     */
    public static function getToleranceRange($pixel,$tolerance)
    {
        $range = array();
        
        foreach($pixel as $channel => $value)
        {
            if($value < 255 && $value > 0)
            {                
                $min = $value - ($tolerance/2);
                $max = $value + ($tolerance/2);
            } 
            elseif($value == 255) 
            {
                $min = $value - $tolerance;
                $max = $value;
            }
            elseif($value == 0)
            {
                $min = $value;
                $max = $value + $tolerance;
            }
            
            $range[$channel] = array('min'=>$min,'max'=>$max);
        }
        
        return $range;
    }
        
    /**
     * Cache System
     */
    public static function isInCache($background) {
        $files = array();
        $ret = false;
        $handle = opendir(__DIR__.self::CACHE_PATH);
        if($handle) {
            while(false !== ($entry = readdir($handle))) {
                if($entry != '.' && $entry != ".."){
                    if($entry == $background){
                        $ret = true;
                        break;
                    }
                }
            }
            closedir($handle);
        }
        return $ret;
    }
}


class HTMLRenderer {

    /**
     * Render Begin Div Tag of Media Container
     */
    static function Begin_MediaContainer() {
        echo "<div id='MediaContainer' class='container'>";
    }
    /**
     * Render Begin Div Tag of Mono Media Container
     */
    static function Begin_MediaContainerMono() {
        echo "<div id=\"MediaContainerMono\" class=\"container\">";
    }
    /**
     * Render Begin Div Tag of Answers Container With File Next To
     */
    static function Begin_AContainer() {
        echo "<div id='AnswerContainer' class='container'>";
    }
    /**
     * Render Begin Div Tag of Mono Answers Container
     */
    static function Begin_AContainer_Mono() {
        echo "<div id='AnswerContainerMono' class='container'>";
    }
    /**
     * Render Begin Div Tag of Fill Gaps Container
     */
    static function Begin_GapsContainer() {
        echo "<div id='FillGapsContainer' class='container'>";
    }
    
    static function Begin_MatchingContainer(){
        echo "<div id='MatchingContainer' class='container'>";
    }
    /**
     * Render End Div Tag Of a Container
     */
    static function End_Container() {
        echo "</div>";
    }

    
    static function Render_NotAvailable()
    {
        echo "<div id=\"message\">
                <div id=\"text\" class=\"box-shadow\">
                    <h1>Preview</h1>
                    <h2>Not Available</h2>
                    <p>This Type Of Question</p>
                    <h3>Coming Soon!</h3>
                </div>
            </div>";
    }
    
    static function Render_MatchingGroup(&$Params, &$ShowAnswers)
    {
        //Params[n] Group
        //Params[n+1] Answers
        $TotalAnswers = array();
        self::Begin_MatchingContainer();
        // Render board
        echo "<div id=\"board\">";
        
        for($i=0;$i<count($Params);$i+=2)
        {
            $Group = $Params[$i];
            $Answers = preg_split('/~/', $Params[$i+1]);

            echo "<div id=\"group-$i\" class=\"dropGroup\">";
            echo "<p>$Group</p>";
            
            for($j=0;$j<count($Answers);$j++) {
                if(!$ShowAnswers) {
                    if($Answers[$j] != '') {
                        $TotalAnswers[] = $Answers[$j];
                        echo "<div id=\"drop\" class=\"dropBox\"></div>";
                    }
                } else {
                    if($Answers[$j] != '') {
                        $rid = mt_rand();
                        echo "<div id=\"drop\" class=\"dropBox\">";
                        echo "<span id=\"drag-$rid\" class=\"dragBox\"><p>$Answers[$j]</p></span>";
                        echo "</div>";
                    }
                }
            }
            
            echo "</div>";
        }
        
        echo "</div>";
        
        // Render Answers
        echo "<div id=\"answers\" class=\"dropBox\">";
        if(!$ShowAnswers) {
            $dragBoxes = array();
            for($i=0;$i<count($TotalAnswers);$i++)
            {
                $j = mt_rand();
                $dragBoxes[] = "<span id=\"drag-$j\" class=\"dragBox\"><p>$TotalAnswers[$i]</p></span>";
            }

            if(count($dragBoxes) > 4) {
                $temp[0] = $dragBoxes[0];
                $temp[1] = $dragBoxes[2];
                $temp[2] = $dragBoxes[4];

                $dragBoxes[0] = $temp[2];
                $dragBoxes[2] = $temp[1];
                $dragBoxes[4] = $temp[0];
            } elseif(count($dragBoxes) > 2) {
                $temp[0] = $dragBoxes[0];
                $temp[1] = $dragBoxes[2];

                $dragBoxes[0] = $temp[1];
                $dragBoxes[2] = $temp[0];
            }

            for($i=0;$i<count($dragBoxes);$i++) {
                echo $dragBoxes[$i];
            }
        }
        echo "</div>";
        self::End_Container();
    }
    
    /**
     * 
     * @param array $answers Matching Answers [Title │ Correct]
     * @param boolean $ShowAnswers Show Correct Answers [TESTWARE Feature]
     */
    static function Render_Matching(&$answers, &$ShowAnswers)
    {
        self::Begin_MatchingContainer();           
        
        $titles = array();
        $corrects = array();
        $dragBoxes = array();
        
        foreach($answers as $answer)
        {
            $splited = preg_split('/│/',$answer);
            $titles[] = $splited[0];
            $corrects[] = $splited[1];
        }

        foreach($corrects as $answer) {
            if($answer == '') continue;
            $i=mt_rand(); // Generate a random number for a unique id.
            $dragBoxes[] = "<span id=\"drag-$i\" class=\"dragBox\"><p>$answer</p></span>";
        }
        
        echo "<div id=\"board\"><div id=\"wraper\">";
        if(!$ShowAnswers) {
            foreach($titles as $title) {
                if($title == '') continue;
                $dropBox = "<div id=\"drop\" class=\"dropBox\"></div>";
                echo "<div class=\"text\"><p>$title</p></div>";
                echo "$dropBox";
            }
        } else {
            for($i=0;$i<count($titles);$i++) {
                $title = $titles[$i];
                $dragBox = $dragBoxes[$i];
                
                if($title == '' || $dragBox == '') continue;
                
                echo "<div class=\"text\"><p>$title</p></div>";
                echo "<div id=\"drop\" class=\"dropBox\">";
                echo $dragBox;
                echo "</div>";
            }
        }
        echo "</div></div>";
        
        echo "<div id=\"answers\" class=\"dropBox\">";
        
        if(!$ShowAnswers) {
            if(count($dragBoxes) > 2) {
                $i=mt_rand(0,count($corrects)-1);
                $j=mt_rand(1,count($corrects)-1);
                $temp[0] = $dragBoxes[$j];
                $temp[1] = $dragBoxes[$i];
                $dragBoxes[$j] = $temp[1];
                $dragBoxes[$i] = $temp[0];
            }

            for($i=0;$i<count($dragBoxes);$i++)
            {
                echo $dragBoxes[$i];
            }
        }
        
        echo "</div>";
        self::End_Container();
    }
    
    /**
     * Render
     * - Static Display Action
     * - HTML Code
     * @param string $file Filename
     * @param QuestionObject $Question
     */
    static function Render_StaticDisplay(&$file, &$Question)
    {
        self::Begin_MediaContainerMono();
            echo self::_Create_MediaHTML($file, $Question);
        self::End_Container();
    }
    
    /**
     * Render
     * - MultiPic_OneCorrect Action
     * - HTML Code
     * @param array $answers Answers
     * @param QuestionObject $Question Current Builder Question
     * @param boolean $ShowAnswers Show Correct Answer(s) [TESTWARE Feature]
     * @param int $correct Correct Answer Index Number
     */
    static function Render_MultiPicOneCorrect(&$answers, &$Question, &$ShowAnswers, &$correct) {
        
        // Render Mono Answers Container
        self::Begin_AContainer_Mono();
        echo "<form id='answersForm' method='post'>";
        
        // Build Pictures Paths
        $answersPath = array();
        foreach($answers as $filename)
        {
            $answersPath[] = 
            Config::BUILDER_URL."/FILES/$Question->GroupID/$Question->ModuleID/$Question->CertID/$filename";
        }
        
        // Render Correct Answers
        if($ShowAnswers) {
            echo "<script>
                    $(document).ready(function(){
                        $('.answer').eq($correct-1).children('label').click();
                    });
                </script>";
        }
        
        // Calculate Geometry
        $height = round(100 / count($answers),2) - 2;
        $style = "height:$height%;";
        
        // Render Answers
        for($i=0;$i<count($answers);$i++)
        {
?>
            <p class="answer" style="<?php echo $style?>">
                <label>
                    <input type="radio" name="group1" value="<?php echo $i+1;?>" />
                    <img src="<?php echo $answersPath[$i]?>" />
                </label>
            </p>
<?php
        }
        
        echo "</form>";
        self::End_Container();
    }
    
    /**
     * Render
     * - Multi Pic File One Correct Action
     * - HTML Code
     * @param array $answers
     * @param QuestionObject $Question
     * @param string $file
     * @param boolean $ShowAnswers
     * @param int $correct
     */
    static function Render_MultiPicFile_OneCorrect(&$answers, &$Question, &$file, &$ShowAnswers, &$correct)
    {        
        // Render Media Container
        self::Begin_MediaContainer();
        
            echo self::_Create_MediaHTML($file, $Question);
        
        self::End_Container();
        
        // Render Answers Container
        self::Begin_AContainer();
        echo "<form id='answersForm' method='post'>";
            
        // Build Pictures Paths
        $answersPath = array();
        foreach($answers as $filename)
        {
            $answersPath[] = Config::BUILDER_URL."/FILES/$Question->GroupID/$Question->ModuleID/$Question->CertID/$filename";
        }
        
        // Render Correct Answers
        if($ShowAnswers) {
            echo "<script>
                    $(document).ready(function(){
                        $('.answer').eq($correct-1).children('label').click();
                    });
                </script>";
        }        
        
        // Calculate Geometry
        $height = round(100 / count($answers),2) - 2;
        $style = "height:$height%;";
        
        // Render Answers
        for($i=0;$i<count($answers);$i++)
        {
?>
            <p class="answer" style="<?php echo $style?>">
                <label>
                    <input type="radio" name="group1" value="<?php echo $i+1;?>" />
                    <img src="<?php echo $answersPath[$i]?>" />
                </label>
            </p>
<?php
        }
        echo "</form>";
        self::End_Container();
    }
    
    
    /**
     * Render
     * - Fill the gaps
     * - HTML Code
     * @param mixed $gaptext Fill Gaps Text
     * @param array $answers Correct Answers
     * @param string $file Fill Gaps File
     * @param boolean $ShowAnswers Render Answers [Testware Feature]
     */
    static function Render_FillGaps(&$gaptext, &$answers, &$file, &$Question ,&$ShowAnswers)
    {
        self::Begin_GapsContainer();
            // Decode HTML Chars
            $pregaps = htmlspecialchars_decode($gaptext);
            // Replace Gaps With Input Tags
            if(!($ShowAnswers)) {
                $outBuf = preg_replace('/│/', "<input type='text' value='' />", $pregaps);
            } else {
                $outBuf = $pregaps;
                for($i=0;$i<count($answers);$i++)
                {
                    $renderAns = $answers[$i];
                    $input = null;

                    if(preg_match('/┼/',$renderAns)) {
                        $renderAns = preg_replace('/┼/', ", ", $renderAns);
                    } else {

                    }

                    $outBuf = preg_replace('/│/', '<input type="text" value="'.$renderAns.'" />', $outBuf, 1);
                }
            }
            // Render HTML
            if($file && $Question) {
                $fileBuf = self::_Create_MediaHTML($file, $Question);
                echo "<div id=\"fillgapstext\">$outBuf</div>";
                echo "<div id=\"fillgapsfile\" class=\"FileContainer\">$fileBuf</div>";
            } else {
                echo $outBuf;
            }

        self::End_Container();
    }
    
    /**
     * Render 
     * - Multiply choice with one correct answer
     * - HTML Code
     * @param array $answers
     */
    static function Render_MultiOneCorrect(&$answers, &$ShowAnswers, &$correct) {
        self::Begin_AContainer_Mono(); //Start of Answers Container
            $height = round(100 / count($answers),2) - 2;
            $style = "height:".$height."%;";

            if($ShowAnswers) {
                echo "<script>
                        $(document).ready(function(){
                            $('.answer').eq($correct-1).children('label').click();
                        });
                    </script>";
            }

            echo "<form id='answersForm' method='post'>";
            for ($i = 0; $i < count($answers); $i++) {
                ?>
                <p class="answer" style="<?php echo $style;?>">
                    <label>
                        <input type="radio" name="group1" value="<?php echo $i+1 ?>" />
                        <span><?php echo $answers[$i]; ?></span>
                    </label>
                </p>
                <?php
            }
            echo "</form>";
        self::End_Container();
    }
    
    /**
     * Render
     * - Multiply choise with two plus correct answers.
     * - HTML Code
     * @param array $answers Answers to Render
     * @param boolean $ShowAnswers Show Answers [Testware Feature]
     * @param string $correct Correct Answers Index
     */
    static function Render_MultiTwoPlusCorrect(&$answers, &$ShowAnswers, &$correct) {
        self::Begin_AContainer_Mono(); //Start of Answers Container
            // Calculate View Space
            $height = round(100 / count($answers),2) - 2;
            $style = "height:$height%;";
            // Render Answers
            if($ShowAnswers)
            {
                $ansIndex = preg_split('/\\\\/',$correct);
                echo "<script>
                    $(document).ready(function(){
                    ";
                // Filter Empty Indexes
                for($i=0;$i<count($ansIndex);$i++)
                {
                    if($ansIndex[$i] != '') {
                        echo "$('.answer').eq($ansIndex[$i]-1).children('label').click();";       
                    }
                }
                echo "});
                    </script>";
            }

            echo "<form id='answersForm' method='post'>";
            for($i = 0; $i < count($answers); $i++) {
            ?>
                <p class="answer" style="<?php echo $style;?>">
                    <label>
                        <input type="checkbox" name="group1" value="<?php echo $i+1; ?>" />
                        <span><?php echo $answers[$i]; ?></span>
                    </label>
                </p>
            <?php
            }
            echo "</form>";
        self::End_Container();
    }
    
    /**
     * Render
     * - Question Title
     * - HTML Code
     * @param string $text
     */
    static function Render_Title($text) {
        echo "<div id='title' class='box-shadow box-shadow-down-ie'><p>$text</p></div>";
    }

    static function Render_MultiTwoFilesOneCorrect(&$answers, &$Question, &$files, &$ShowAnswers, &$correct) {
        // Render Media Container
        self::Begin_MediaContainer();
            
            // MultiFiles
            foreach($files as $file)
            {
                echo "<div class=\"FileContainer\">".self::_Create_MediaHTML($file, $Question)."</div>";
            }
        
        self::End_Container();
        
        // Render Answer Container
        self::Begin_AContainer();
        
            echo "<form id='answersForm' method='post'>";
            
            if($ShowAnswers)
            {
                echo "<script>
                    $(document).ready(function(){
                        $('.answer').eq($correct-1).children('label').click();
                    });
                    </script>";
            }
            
            $height = round(100 / count($answers),2) - 2;
            $style = "height:$height%;";
            
            for($i=0;$i<count($answers);$i++)
            {
?>
                <p class="answer" style="<?php echo $style;?>">
                    <label>
                        <input type="radio" name="group1" value="<?php echo $i+1;?>" />
                        <span><?php echo $answers[$i];?></span>
                    </label>
                </p>
<?php
            }
            
            echo "</form>";
        
        self::End_Container();
    }
    
    
    /**
     * Render
     * - Multiply choice with file and one correct answer
     * - HTML Code
     * @param array $args
     */
    static function Render_MultiFileOneCorrect(&$answers, &$Question, &$file, &$ShowAnswers, &$correct) {
    
        
        self::Begin_MediaContainer(); // Start of File Container

            echo self::_Create_MediaHTML($file, $Question);

        self::End_Container(); // End of File Container

        self::Begin_AContainer(); // Start of Answers Container
            
            echo '<form id="answersForm" method="post">';

            if($ShowAnswers)
            {
                echo "<script>
                    $(document).ready(function(){
                        $('.answer').eq($correct-1).children('label').click();
                    });
                    </script>";
            }

            // Calculate View Space
            $height = round(100 / count($answers),2) - 2;
            $style = "height:$height%;";
            for ($i = 0; $i < count($answers); $i++) {
                ?>
                <p class="answer" style="<?php echo $style;?>">
                    <label>
                        <input type="radio" name="group1" value="<?= $i+1; ?>" />
                        <span><?php echo $answers[$i]; ?></span>
                    </label>
                </p>
                <?php
            }
            echo '</form>';

        self::End_Container(); // End of Answers Container
    }
    
    /**
     * Render
     * - Drag and Drop question
     * - HTML Code
     */
    public static function Render_DragNDrop(&$Question, &$file, &$background, &$cellsize, &$pictures, &$ShowAnswers) {
        
        echo '<div id="DND" class="container">';
        
        // Background Container.
        echo '<div id="background_container" dnd_cellsize="'.$cellsize.'">';
            echo self::_Create_MediaHTML($background, $Question);
        echo '</div>';
        
        // Pictures Container.
        echo '<div id="pictures_container">';
            $filenames = array_keys($pictures);
            foreach($filenames as $picfile){
                echo self::_Create_MediaHTML($picfile, $Question);
            }
        echo '</div>';
        
        // Sound Container
        echo '<div id="sound_container">';        
            echo self::_Create_MediaHTML($file, $Question);
        echo '</div>';
        
        echo '</div>';
        
        // Load DND Javascript Library
        echo '<script src="js/DND.js"></script>';
    }
    
    /**
     * Render
     * - Fill Color Question Type
     * - HTML Code
     * @param QuestionObject $Question
     * @param string $file
     * @param string $background
     * @param array $colors [colorname]=>[pixel]
     * @param boolean $ShowAnswers
     */
    public static function Render_FillColor(&$Question, &$file, &$background, &$pixels, &$ShowAnswers) {
        
        $bgSrcURL = Config::BUILDER_URL . "/FILES/{$Question->GroupID}/{$Question->ModuleID}/{$Question->CertID}/$background";
                
        if(FTC::isInCache($background)==false){
            $imageData = file_get_contents($bgSrcURL);
            file_put_contents(__DIR__.FTC::CACHE_PATH."/$background", $imageData);
        }
        
        echo '<div id="FTC" class="container">';
        
            echo '<div id="draw_container">';
                echo '<canvas id="drawBoard">Your Browser Doesn\'t support <b>HTML5 Canvas Element</b>.</canvas>';
            echo '</div>';

            
            echo '<div id="palete_container">';
                FTC::GenerateColorPalete();
            echo '</div>';
        
            
            echo '<div id="sound_container">';
                echo self::_Create_MediaHTML($file, $Question);
            echo '</div>';
            
            echo '<script src="js/FTC.js"></script>';
            echo '<script>
                    $(window).load(function(){
                        FTC.init('.json_encode($pixels).',"cache/ftc/'.$background.'");
                    });
                </script>';
        echo '</div>';
    }
    
    /**
     * Create MediaContainers InnerHTML Code For Media File
     * @param string $file Filename
     * @param QuestionObject $Question Current Builder Question
     * @return string HTML Code
     */
    private static function _Create_MediaHTML(&$file, &$Question)
    {
        // Get Media File
        $GID = $Question->GroupID;
        $MID = $Question->ModuleID;
        $CID = $Question->CertID;

        $file = rawurlencode($file);
        
        $fileURI = Config::BUILDER_URL . "/FILES/$GID/$MID/$CID/".$file;
        
        $headers = get_headers($fileURI, 1);
        $fileType = $headers['Content-Type']; 
       
        // Render Media Container
        $retBuf = null;
        //echo $fileType;
        
        // Forbidden Error
        if(!preg_match('/200/', $headers[0])){
            return null;
        }
        
        // If We Have a Audio
        if(preg_match('/audio/', $fileType))
        {
            $retBuf = "
                <audio controls autoplay class=\"player\">
                    <source src=\"$fileURI\" type=\"$fileType\">
                </audio>
            ";
        }
        // If We Have a Video
        elseif(preg_match('/video/', $fileType))
        {
            $retBuf = "
                <video controls autoplay class=\"player\">
                    <source src=\"$fileURI\" type=\"$fileType\">
                </video>
                ";
        }
        // If We Have a Image
        elseif(preg_match('/image/', $fileType))
        {
            $retBuf = "
                <img src=\"$fileURI\" />
                ";
        }
        // If We Have a Text
        elseif(preg_match('/text/', $fileType))
        {
            if(($text = @file_get_contents($fileURI))) {
                // HTML Code
                if(preg_match('/html/', $fileType))
                {
                    // Seperate HTML Document by <body> tags
                    $sep = preg_split('/<body>/i', $text);
                    $sep = preg_split('/<\/body>/i', $sep[1]);

                    $retBuf = '<div id="TextContainer">' . $sep[0] . '</div>';
                }
                // Everything Else
                else {
                    $retBuf = "<div id=\"TextContainer\">$text</dic>";
                }
            }
        }
        
        return $retBuf;
    }
}
?>
