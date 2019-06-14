<?php

include_once __DIR__.'/config.php';

$Actions = array(
    'MULTI_TWO_FILES_ONE_CORRECT' => array(
        array(
                  'QID' => 8,
                  'MID' => 9999,
                  'CID' => 1
        )  
    ),
    'MATCHING_GROUPS' => array(
        array(
                'QID' => 3,
                'MID' => 9999,
                'CID' => 1
        )
    ),
    'MATCHING' => array(
        array(
                'QID' => 2,
                'MID' => 9999,
                'CID' => 1
        )
    ),
    'MULTI_PIC_ONE_CORRECT' => array(
        array(
                'QID' => 6,
                'MID' => 9999,
                'CID' => 1
        )
    ),
    'MULTI_TWO_PLUS_CORRECT' => array(
        array(
                'QID' => 5,
                'MID' => 9999,
                'CID' => 1
        )
    ),
    'FILL_GAPS' => array(
        array(
                'QID' => 1,
                'MID' => 9999,
                'CID' => 1
            ),
        ),
    'STATIC_DISPLAY' => array(
        array(
                'QID' => 10,
                'MID' => 9999,
                'CID' => 1
            ),
    ),
    'MultiPicFile_OneCorrect' => array(
        array(
                'QID' => 741,
                'MID' => 1002,
                'CID' => 1
        ),
    ),
    'MULTIFILE_ONECORRECT' => array(
        array(
                'QID' => 7,
                'MID' => 9999,
                'CID' => 1
        ),
    ),
    'MULTI_ONECORRECT' => array(
        array(
                'QID' => 4,
                'MID' => 9999,
                'CID' => 1
        ),
    ),
    'DRAG_N_DROP' => array (
        array(
                'QID' => 12,
                'MID' => 9999,
                'CID' => 1
        ),
    ),
    'FILL_COLOR' => array (
        array(
                'QID' => 425,
                'MID' => 2001,
                'CID' => 1
        )
    )
);
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Actions Tester</title>
    </head>
    
    <body>
       <p style="font-size:15pt;">Current Builder: <b><?php echo Config::BUILDER_URL;?></b></p>
       <table style="width:50%;">
                <tr>
                    <th>Action</th><th>Link</th>
                </tr>
                <?php
                    $index = 0;
                    foreach($Actions as $key=>$value)
                    {
                        $index++;
                        echo "<tr>
                                <td>$index) $key</td>
                                <td style=\"text-align:center;\">
                                    <form action='preview.php' method='get' target='_blank'>
                                        <input type='hidden' name='QID' value='".$value[0]['QID']."' />
                                        <input type='hidden' name='MID' value='".$value[0]['MID']."' />
                                        <input type='hidden' name='CID' value='".$value[0]['CID']."' />
                                        <input type='hidden' name='PS' value='123456' />
                                        <button>Preview!</button>
                                    </form>
                                </td>
                             </tr>";
                    }
                ?>

       </table>
    </body>
</html>