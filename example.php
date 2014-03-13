<?php 

$colorExtractor = ColorExtractor::createFromURL("HueColorExtractor", "Tulips.jpg");
$color = $colorExtractor->extract();

?>

<div style="width : 500px; height : 500px; background-color : <?php echo $color->getHTMLCode(); ?>">

</div>