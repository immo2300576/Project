<!DOCTYPE html>
<meta charset="UTF-8"> 
<html>
<head>
<style>
    form {
        background-color: #F5F5F5;
        width: 350px;
        height: 180px;
        margin: 0 auto;
        border: 1px solid #DEDEDE
    }
    hr {
        border-color: #DEDEDE;
        background-color:#DEDEDE;
        color:#DEDEDE;
    }
    h2 {
        margin-top: 3px;
        margin-bottom: 0px;
    }
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    th {
        width: 350px;
        height: 25px;
        background-color: #F3F3F3;
        text-align: left;
    }
    td {
        width: 650px;
        background-color: #FBFBFB;
        text-align: center;  
    }
    #newsTable {
        border: 1px solid #DEDEDE;
        display: none;
        text-align: left;
    }
    td.news {
        width: 1000px;
        height: 25px;
        text-align: left;
    }
    #link1, #link2, #link3, #link4, #link5 {
        text-decoration:none;
    }
    .indicator {
        color:blue;
    }
    .indicator:hover {
        cursor:pointer;
        color:black;
    }
    .indicator:active {
        cursor:pointer;
        color:black;
    }
    a:link {
        color: blue;
    }
    a:visited {
        color: blue;
    }
    a:hover {
        color: black;
    }
    a:active {
        color: black;
    }    
    span {
        padding: 10px;
        color:blue;
    }
</style>
<script type="text/javascript">
    // clear the text and the below field
    function clearAll() {    
        location.assign(location.href);
    }
    function setAliveAll() {
        document.getElementById("stock_symbol").innerHTML= symbol;
        document.getElementById("table").style.display="table";
        document.getElementById("container").style.display="div";
        document.getElementById("news").style.display="div";
    }
    function test() {
        alert("test");
    }
</script>
</head>
<body>

<form method="POST" id="myForm" action="" style="text-align:center" align="center">
<h2>Stock Search</h2>
<hr> 
<p align="left">Enter Stock Ticker Symbol:*
<input type="text" name="stock_symbol" id="stock_symbol"
       value="<?php echo isset($_POST["stock_symbol"]) ? $_POST["stock_symbol"] : "" ?>"></p>
    <input type="submit" name="submit" value="Search" style="margin-left:100px;padding:0px;width:100px">
    <input type="button" name="clear" value="clear" onclick="clearAll()" style="padding:0px;width:100px">
<p align="left">*-<i>Mandatory fields</i></p>
</form>
<br>
<?php
    // set timezone
    if( ! ini_get('date.timezone') ) {
        date_default_timezone_set('America/New_York'); // GMT
    }

    function alert($msg) {
        echo "<script type='text/javascript'>alert('$msg');</script>";
    }
       
    if (isset($_POST["submit"])) :
        $apiKey = "AGOCH7W6TEP57Y37";
        $function = "TIME_SERIES_DAILY";
        if ($_POST["stock_symbol"]=="") :
            alert("Please enter a symbol");
        else :
            // load json
            $url = "https://www.alphavantage.co/query?function=" . $function . "&symbol=" . $_POST["stock_symbol"] . "&outputsize=full&apikey=" . $apiKey;
            try {
                $json = file_get_contents($url);
                $array = json_decode($json, true);
            }
            catch(Exception $e) {
                echo 'Server is busy. Please try later.';
                exit;
            }
            if (!array_key_exists('Meta Data', $array)) :
                echo "<div align=center><table>";
                echo "<tr><th>Error</th><td>Error: NO record has been found, please enter a valid symbol</td></tr>";
                echo "</table></div>";
            else :
                $currentDate = $array['Meta Data']['3. Last Refreshed'];
                $dateData = new DateTime($currentDate);
                $currentDate = $dateData->format('Y-m-d');
                $xsize = 130;//sizeof($array["Time Series (Daily)"]);
                $xaxis = array();
                $priceData = array();
                $volumnDate = array();

                for ($i = $xsize-1; $i >=0;) {
                    // echo $dateData->format('Y-m-d');
                    if (array_key_exists($dateData->format('Y-m-d'), $array["Time Series (Daily)"])) {
                        $xaxis[] = $dateData->format('m/d');
                        $priceData[] = round($array["Time Series (Daily)"][$dateData->format('Y-m-d')]['4. close'],2);
                        $volumnData[] = floatval($array["Time Series (Daily)"][$dateData->format('Y-m-d')]['5. volume']);
                        
                        if ($i == $xsize-2)
                            $preDate = $dateData->format('Y-m-d');
                        
                        $dateData->sub(new DateInterval('P1D'));
                        $i--;
                    }
                    else {
                        $dateData->sub(new DateInterval('P1D'));
                    }
                }
                // load xml
                try {
                    $xmlUrl = "https://seekingalpha.com/api/sa/combined/" . $_POST["stock_symbol"] . ".xml";
                    
                    $url_headers = @get_headers($xmlUrl);
                    if($url_headers[0] == 'HTTP/1.1 200 OK') {
                        $xml = simplexml_load_file($xmlUrl);
                    } 
                    else {
                            // Error
                        echo 'Server is busy. Please try later.';
                        exit("failed to load XML");
                    }
                }
                catch (Exception $e) {
                    echo 'Server is busy. Please try later.';
                    exit;
                }
                // encode json 
                $xml_array = array();
                for ($i = 0; $i < 5; $i++) {
                    $xml_array[] = array($xml->channel->item[$i]->title, $xml->channel->item[$i]->link, $xml->channel->item[$i]->pubDate);
                }
                $vol = intval($array["Time Series (Daily)"][$currentDate]['5. volume']);
       
                $ptr = ($vol-$vol%1000000)/1000000;
                $volumeStr = ''; 
                if ($ptr > 0) {
                    $volumeStr = $volumeStr . $ptr . ',';
                    $vol = $vol%1000000;
                }
                $ptr = ($vol-$vol%1000)/1000;
                $volumeStr = $volumeStr . $ptr . ',';
                $vol = $vol%1000;
                $volumeStr = $volumeStr . $vol;
                //$array["Time Series (Daily)"][$currentDate]['5. volume']
                
?>
<table align="center" id="table">
    <tr>
        <th>Stock Ticker Symbol</th>
        <td><?php echo $array['Meta Data']['2. Symbol'] ?></td>
    </tr>
    <tr>
        <th>Close</th>
        <td><?php echo $array["Time Series (Daily)"][$currentDate]['4. close']; ?></td>
    </tr>
    <tr>
        <th>Open</th>
        <td><?php echo $array["Time Series (Daily)"][$currentDate]['1. open']; ?></td>
    </tr>
    <tr>
        <th>Previous Close</th>
        <td><?php echo $array["Time Series (Daily)"][$preDate]['4. close']; ?></td>
    </tr>
    <tr>
        <th>Change</th>
        <?php $change=$array["Time Series (Daily)"][$currentDate]['4. close']-$array["Time Series (Daily)"][$preDate]['4. close']; ?>
        <td><?php echo round($change,2);
            if ($change > 0): 
                $imageURL = "http://cs-server.usc.edu:45678/hw/hw6/images/Green_Arrow_Up.png";
            else: 
                $imageURL = "http://cs-server.usc.edu:45678/hw/hw6/images/Red_Arrow_Down.png";
            endif ?>
            <img src=<?php echo $imageURL; ?> height="10px" width="10"/></td>
    </tr>
    <tr>
        <th>Change Percent</th>
        <?php if ($array["Time Series (Daily)"][$preDate]['4. close'] != 0):
                $rate = $change/$array["Time Series (Daily)"][$preDate]['4. close'];
              else : $rate = 0.00;
        endif?>
        <td><?php echo round($rate*100,2)."%"; ?>
            <img src=<?php echo $imageURL; ?> height="10" width="10"/></td>
    </tr>
    <tr>
        <th>Day's Range</th>
        <td><?php echo $array["Time Series (Daily)"][$currentDate]['3. low'] . "-" . $array["Time Series (Daily)"][$currentDate]['2. high']?></td>
    </tr>
    <tr>
        <th>Volume</th>
        <td><?php echo $volumeStr ?></td>
    </tr>
    <tr>
        <th>Timestamp</th>
        <td><?php echo $currentDate ?></td>
    </tr>
    <tr>
        <th>Indicators</th>
        <td><span class = "indicator" onclick="priceChart()">Price</span><span class = "indicator" onclick="singleChart('SMA')">SMA</span>
            <span class = "indicator" onclick="singleChart('EMA')">EMA</span><span class = "indicator" onclick="doubleChart('STOCH')">STOCH</span>
            <span class = "indicator" onclick="singleChart('RSI')">RSI</span><span class = "indicator" onclick="singleChart('ADX')">ADX</span>
            <span class = "indicator" onclick="singleChart('CCI')">CCI</span><span class = "indicator" onclick="tripleChart('BBANDS')">BBANDS</span>
            <span class = "indicator" onclick="tripleChart('MACD')">MACD</span>
        </td>
    </tr>
</table>
    
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<br>
<div id="container" style="width:1000px; height:550px; margin: 0 auto; border: 1px solid #DEDEDE"></div>

<div id="news" align="center">
    <p id="newsText" text-align="center" onClick="newsShowOrHide()">click to show stock news</p>
    <img id= "newsImg" src="http://cs-server.usc.edu:45678/hw/hw6/images/Gray_Arrow_Down.png"  height="15px" width="15px" onClick="newsShowOrHide()">
    <table id="newsTable" style="display:none">
        <tr><td class = "news"><a id = "link1" href="">news1</a> <p id="time1" style="display:inline">time1</p></td></tr>
        <tr><td class = "news"><a id = "link2" href="">news2</a> <p id="time2" style="display:inline">time2</p></td></tr>
        <tr><td class = "news"><a id = "link3" href="">news3</a> <p id="time3" style="display:inline">time3</p></td></tr>
        <tr><td class = "news"><a id = "link4" href="">news4</a> <p id="time4" style="display:inline">time4</p></td></tr>
        <tr><td class = "news"><a id = "link5" href="">news5</a> <p id="time5" style="display:inline">time5</p></td></tr>
    </table>
</div>
<script> 
    
    var count = 0;
    function newsShowOrHide() {
        if (count % 2 == 0) {
            document.getElementById("newsText").innerHTML = "click to hide stock news";
            document.getElementById("newsImg").src = "http://cs-server.usc.edu:45678/hw/hw6/images/Gray_Arrow_Up.png";
            document.getElementById("newsTable").style.display = "table";
        }
        else {
            document.getElementById("newsText").innerHTML = "click to show stock news";
            document.getElementById("newsImg").src = "http://cs-server.usc.edu:45678/hw/hw6/images/Gray_Arrow_Down.png";
            document.getElementById("newsTable").style.display = "none";
        }
        count++;
    }
    var nchart = 0;
    var apiKey = "AGOCH7W6TEP57Y37";
    var xsize = 130;
    var symbol = <?php echo json_encode($_POST["stock_symbol"]); ?>;
    var currentDate = <?php echo json_encode($currentDate); ?>;
    var cateData = <?php echo json_encode(($xaxis)); ?>;
    var priceData = <?php echo json_encode(array_reverse($priceData)); ?>;
    var max = Math.max.apply(null, priceData)*1.1;
    var min = Math.min.apply(null, priceData)*0.9;
    var volumnData = <?php echo json_encode(array_reverse($volumnData)); ?>;
    // news part
    var newsData = <?php echo json_encode($xml_array); ?>;
    for (var i = 1; i < 6; i++) {
        document.getElementById("link"+i).innerHTML = newsData[i-1][0][0];
        document.getElementById("link"+i).href = newsData[i-1][1][0];
        document.getElementById("time"+i).innerHTML = "    Publicated Time: " + newsData[i-1][2][0].substring(0, newsData[i-1][2][0].length-5);
        document.getElementById("time"+i).style.marginLeft = "50px";
    }
    
    
    var source = 'Source: Alpha Vantage';
    var srcLink = source.link("https://www.alphavantage.co");
    
    priceChart();
    
    function priceChart() {
        Highcharts.chart('container', {
            title: {
                text: "Stock Price (" + currentDate + ")"
            },
            subtitle: {
                text: srcLink,
                style: {
                    color: 'blue'
                }
            },
            xAxis: {
                categories: cateData,
                tickInterval: 5,
                reversed: true
            },
            yAxis: [{ 
                title: {
                    text: 'Stock Price',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                labels: {
                    formatter: function () {
                        return this.value.toFixed(2);
                    },
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                //max: max,
                min: min,
                tickAmount: 8
                },
                { // Primary yAxis
                gridLineWidth: 0,
                labels: {
                    formatter: function () {
                        return this.value/1000000 + 'M';
                    },
                    style: {
                        color: Highcharts.getOptions().colors[2]
                    }
                },
                title: {
                    text: 'Volume',
                    style: {
                        color: Highcharts.getOptions().colors[2]
                    }
                },
                min: 0,
                max: 350000000,
                tickInterval: 50000000,
                tickAmount: 8,
                opposite: true

            }],
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            series: [{
                type: 'area',
                name: symbol,
                yAxis: 0,
                data: priceData,
                color: '#ED7D88'
            }, {
                type: 'column',
                yAxis: 1,
                name: symbol + " volume",
                data: volumnData,
                color: '#FFFFFF'
            }]
        });
    }
    function formatDate(date) {
        var day = date.getDate()+1;
        var month = date.getMonth()+1;
        var year = date.getFullYear();
        
        return year + '-' + month + '-' + day;
    }
    function singleChart(indicator) {
        var url = "https://www.alphavantage.co/query?function=" + indicator + "&symbol=" + symbol 
                    + "&interval=daily&time_period=10&series_type=close&apikey=" + apiKey;
        
        var xmlhttp = false;
        if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
            try {
                xmlhttp=new XMLHttpRequest(); 
            }
            catch(e) { xmlhttp = false;}
        }
        else {
            // code for IE6, IE5
            try {
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); 
            }
            catch(e) {xmlhttp = false;}
        }
                
        if (url) {
            xmlhttp.open("GET",url,true); //open, send, responseText are 
            xmlhttp.send("");
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4) {
                    if (xmlhttp.status == 200) {
                       var parseData =  JSON.parse(xmlhttp.responseText);
                       var title = parseData['Meta Data']['2: Indicator'];
                       var date = new Date(currentDate);
                       var data = new Array(xsize);
                       var dataTitle = "Technical Analysis: " + indicator;
        
        
                    for (var i = xsize; i != 0;) {
                        if ((formatDate(date) in parseData[dataTitle])) {
                            data[xsize-i] = parseFloat(parseData[dataTitle][formatDate(date)][indicator]);
                            i--;
                        }
                        date.setDate(date.getDate()-1);
                    }

                    Highcharts.chart('container', {
                        title: {
                            text: title
                        },
                        subtitle: {
                            text: srcLink,
                            style: {
                                color: 'blue'
                            }
                        },
                        xAxis: {
                            categories: cateData,
                            tickInterval: 5,
                            reversed: true
                        },
                        yAxis: { 
                            title: {
                                text: indicator
                            },
                            labels: {
                                format: '{value}'
                            }
                        //max: max,
                        // min: min,
                        // tickAmount: 8
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'middle'
                        },
                        plotOptions: {
                            series: {
                                marker: {
                                    enabled: true,
                                    radius: 3
                                }
                            }
                        },
                        series: [{
                            name: symbol,
                            data: data
                        }]
            
                    });
                    }
                }
            };
        }
    }
    function doubleChart(indicator) {
        var url = "https://www.alphavantage.co/query?function=" + indicator + "&symbol=" + symbol 
                    + "&interval=daily&time_period=10&series_type=close&apikey=" + apiKey;
        
        var xmlhttp = false;
        if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
            try {
                xmlhttp=new XMLHttpRequest(); 
            }
            catch(e) { xmlhttp = false;}
        }
        else {
            // code for IE6, IE5
            try {
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); 
            }
            catch(e) {xmlhttp = false;}
        }
                
        if (url) {
            xmlhttp.open("GET",url,true); //open, send, responseText are 
            xmlhttp.send("");
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4) {
                    if (xmlhttp.status == 200) {
                       var parseData =  JSON.parse(xmlhttp.responseText);
                       var title = parseData['Meta Data']['2: Indicator'];
                       var date = new Date(currentDate);
                       var data1 = new Array(xsize);
                       var data2 = new Array(xsize);
                       var dataTitle = "Technical Analysis: " + indicator;
                       var indicatorIdx = ['SlowK', 'SlowD'];
        
                    for (var i = xsize; i != 0;) {
                        if ((formatDate(date) in parseData[dataTitle])) {
                            data1[xsize-i] = parseFloat(parseData[dataTitle][formatDate(date)][indicatorIdx[0]]);
                            data2[xsize-i] = parseFloat(parseData[dataTitle][formatDate(date)][indicatorIdx[1]]);
                            i--;
                        }
                        date.setDate(date.getDate()-1);
                    }

                    Highcharts.chart('container', {
                        title: {
                            text: title
                        },
                        subtitle: {
                            text: srcLink,
                            style: {
                                color: 'blue'
                            }
                        },
                        xAxis: {
                            categories: cateData,
                            tickInterval: 5,
                            reversed: true
                        },
                        yAxis: { 
                            title: {
                                text: indicator
                            },
                            labels: {
                                format: '{value}'
                            }
                        //max: max,
                        // min: min,
                        // tickAmount: 8
                        },
                        plotOptions: {
                            series: {
                                marker: {
                                    enabled: true
                                }
                            }
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'middle'
                        },
                        series: [{
                            name: indicatorIdx[0],
                            data: data1
                        },{
                            name: indicatorIdx[1],
                            data: data2
                        }]
            
                    });
                    }
                }
            };
        }
    }
    function tripleChart(indicator) {
        var url = "https://www.alphavantage.co/query?function=" + indicator + "&symbol=" + symbol 
                    + "&interval=daily&time_period=10&series_type=close&apikey=" + apiKey;
        
        var xmlhttp = false;
        if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
            try {
                xmlhttp=new XMLHttpRequest(); 
            }
            catch(e) { xmlhttp = false;}
        }
        else {
            // code for IE6, IE5
            try {
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); 
            }
            catch(e) {xmlhttp = false;}
        }
                
        if (url) {
            xmlhttp.open("GET",url,true); //open, send, responseText are 
            xmlhttp.send("");
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4) {
                    if (xmlhttp.status == 200) {
                       var parseData =  JSON.parse(xmlhttp.responseText);
                       var title = parseData['Meta Data']['2: Indicator'];
                       var date = new Date(currentDate);
                       var data1 = new Array(xsize);
                       var data2 = new Array(xsize);
                       var data3 = new Array(xsize);
                       var dataTitle = "Technical Analysis: " + indicator;
        
                       var indicatorIdx;
                       if (indicator == 'MACD') {
                            indicatorIdx = ['MACD_Signal', 'MACD', 'MACD_Hist'];
                       }
                       else { //if (indicator == 'BBANDS') {
                            indicatorIdx = ['Real Middle Band', 'Real Lower Band', 'Real Upper Band'];
                       }
        
        
                    for (var i = xsize; i != 0;) {
                        if ((formatDate(date) in parseData[dataTitle])) {
                            data1[xsize-i] = parseFloat(parseData[dataTitle][formatDate(date)][indicatorIdx[0]]);
                            data2[xsize-i] = parseFloat(parseData[dataTitle][formatDate(date)][indicatorIdx[1]]);
                            data3[xsize-i] = parseFloat(parseData[dataTitle][formatDate(date)][indicatorIdx[2]]);
                            i--;
                        }
                        date.setDate(date.getDate()-1);
                    }

                    Highcharts.chart('container', {
                        title: {
                            text: title
                        },
                        subtitle: {
                            text: srcLink,
                            style: {
                                color: 'blue'
                            }
                        },
                        xAxis: {
                            categories: cateData,
                            tickInterval: 5,
                            reversed: true
                        },
                        yAxis: { 
                            title: {
                                text: indicator
                            },
                            labels: {
                                format: '{value}'
                            }
                        //max: max,
                        // min: min,
                        // tickAmount: 8
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'middle'
                        },
                        plotOptions: {
                            series: {
                                marker: {
                                    enabled: true
                                }
                            }
                        },
                        series: [{
                            name: indicatorIdx[0],
                            data: data1
                        }, {
                            name: indicatorIdx[1],
                            data: data2
                        }, {
                            name: indicatorIdx[2],
                            data: data3
                        }]
            
                    });
                    }
                }
            };
        }
    }
</script>
<?php endif;endif;endif; ?>
<noscript>Sorry, your browser does not support JavaScript!</noscript>
</body>
</html>
