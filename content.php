<?PHP
    class content
    {
        public static function createMenu()
        {
            //Should get these from db (dynamic)
            echo "<li id='current'><a href='index.html'>Home</a></li>";
            echo "<li><a href='style.html'>Style Demo</a></li>";
            echo "<li><a href='blog.html'>Blog</a></li>";
            echo "<li><a href='archives.html'>Archives</a></li>";
            echo "<li><a href='index.html'>Support</a></li>";
            echo "<li><a href='index.html'>About</a></li>";
        }
        
        //Creates featured items based on result
        public static function createFeaturedItems($result)
        {
            $content = "";

            while ($row = mysql_fetch_assoc($result))
            {
                //These should be set for each item
                $title = "";
                $subtitle = "";
                $text = "";
                $imagePath = "";
                
                $table = mysql_tablename($result, $row); //not sure at all if this works
                if($table == "featured")
                {
                    $table = $row["table"]; //need a table named 'featured' with these columns: table, id, posted
                    $row = ""; //Set row to that item (table + id)
                }
                switch($table)
                {
                    //Set title, image
                    case "maps":
                        $title = $row["title"];
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = $row["description"];
                        $imagePath = $row["minimap"];
                        break;
                    case "units":
                        $title = $row["title"];
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = "";
                        $imagePath = "";
                        break;
                    case "guide":
                        $title = $row["title"];
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = "";
                        $imagePath = "";
                        break;
                }
                //Should get these from db
                $content .= "<div id='featured-block' class='clear'>";
                $content .= "<div id='featured-ribbon'></div>	";
                $content .= "<a name='TemplateInfo'></a>";
                
                if($imagePath.length() > 0)
                {
                    $content .= "<div class='image-block'>";
                    $content .= "<a href='index.html' title=''><img src='" . $imagePath . "' alt='featured' width='350px' height='250px'/></a>";
                    $content .= "</div>";
                }
                
                $content .= "<div class='text-block'>";
                $content .= "<h2><a href='index.html'>" . $title . "</a></h2>"; //index.html? could it be something else..
                $content .= "<p class='post-info'>" . $subtitle . "</p>";
                $content .= "<p>" . $text . "</p>";
                $content .= "<p><a href='index.html' class='more-link'>Read More</a></p>"; //index.html? could it be something else..
                                                                                           //All use read more button?
                $content .= "</div>";
                $content .= "</div>";
            }
            
            return $content;
        }
    }
?>