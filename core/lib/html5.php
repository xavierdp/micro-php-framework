<?php

function _attr($attr)
{
    // return preg_replace('%{{[^}]*}}%', '|_%1_|', $attr);
    return str_replace(array("|_", "_|"), array('{{', '}}'), str_replace(array("{", "}"), array('="', '"'), str_replace(array("{{", "}}"), array('|_', '_|'), $attr)));

}

$a_tag = array
    (
    "main",
    "template",
    "a",
    "abbr",
    "address",
    "area",
    "article",
    "aside",
    "audio",
    "b",
//     "base",            autoclosed
     "bdi",
    "bdo",
    "blockquote",
    "body",
//     "br",            autoclosed
     "button",
    "canvas",
    "caption",
    "cite",
    "code",
    "col",
    "colgroup",
    "command",
    "data",
    "datalist",
    "dd",
    "del",
    "details",
    "dfn",
    "div",
    "dl",
    "dt",
    "em",
    "fieldset",
    "figcaption",
    "figure",
    "footer",
    "form",
    "h1",
    "h2",
    "h3",
    "h4",
    "h5",
    "h6",
    "head",
    "header",
    "hgroup",
//     "hr",            autoclosed
     //     "html",            redefined
     "i",
    "iframe",
//     "img",            autoclosed redefined
     //     "input",  autoclosed
     "ins",
    "kbd",
    "keygen",
    "label",
    "legend",
    "li",
//     "link",            autoclosed
     "map",
    "mark",
    "math",
    "menu",
//     "meta",            autoclosed
     "meter",
    "nav",
    "noscript",
    "object",
    "ol",
    "optgroup",
    "option",
    "output",
    "p",
//     "param",         autoclosed
     "pre",
    "progress",
    "q",
    "rp",
    "rt",
    "ruby",
    "s",
    "samp",
    "script",
    "section",
    "select",
    "small",
    "source",
    "span",
    "strong",
//     "style",         redefined
     "sub",
    "sup",
    "summary",
    "svg",
    "table",
    "tbody",
    "td",
    "textarea",
    "tfoot",
    "th",
    "thead",
//     "time",            input
     "title",
    "tr",
    "track",
    "u",
    "ul",
    "var",
    "video",
//     "wbr",            autoclosed
);

foreach ($a_tag as $tag)
{
    eval('function _' . $tag . '()
	{
		$a_arg = func_get_args();

		if(func_num_args() == 2)
		{
			return	"<' . $tag . ' "._attr($a_arg[0]).">$a_arg[1]</' . $tag . '>";
		}

		return  "<' . $tag . '>$a_arg[0]</' . $tag . '>";
	}');
}

unset($a_tag);

function _html()
{
    $a_arg = func_get_args();

    if (func_num_args() == 2)
    {
        return "<!doctype html><html " . _attr($a_arg[0]) . ">$a_arg[1]</html>";
    }

    return "<!doctype html><html>$a_arg[0]</html>";
}

function _doctype()
{
    return "<!doctype html>";
}

// inputs

$a_tag = array
    (
    "button",
    "checkbox",
    "color",
    "date",
    "datetime",
//     "datetime-local", manualy defined cause hyphen
     "email",
    "file",
    "hidden",
    "image",
    "month",
    "number",
    "password",
    "radio",
    "range",
    "reset",
    "search",
    "submit",
    "tel",
    "text",
    "time",
    "url",
    "week",

);

foreach ($a_tag as $tag)
{

    eval('function _i_' . $tag . '($attr = null)
	{
		if($attr)
		{
			return "<input type=\"' . $tag . '\" "._attr($attr)."/>";
		}

		return "<input type=\"' . $tag . '\" />";
	}');
}

function _i_datetime_local($attr = null)
{
    if ($attr)
    {
        return '<input type="datetime-local" ' . _attr($attr) . '>';
    }

    return '<input type="datetime-local">';
}

// autoclosed

$a_tag = array
    (
    "link",
    "meta",
    "hr",
    "br",
    "wbr",
    "input",
    "base",
    "param",
    "img",
    "path",
);

foreach ($a_tag as $tag)
{
    eval('function _' . $tag . '($attr = null)
	{
		if($attr)
		{
			return "<' . $tag . ' "._attr($attr)." />";
		}

		return "<' . $tag . ' />";

	}');
}

// function _img()
// {
//     $a_arg = func_get_args();

//     if (func_num_args() == 2)
//     {
//         return '<img src="' . $a_arg[1] . '" ' . _attr($a_arg[0]) . '/>';
//     }

//     return '<img src="' . $a_arg[0] . '"/>';
// }

// function _base()
// {
//     $a_arg = func_get_args();

//     if (func_num_args() == 2)
//     {
//         return '<base href="' . $a_arg[1] . '" ' . _attr($a_arg[0]) . ' />';
//     }

//     return '<base href="' . $a_arg[0] . '" />';
// }

// customized

function _style()
{
    $a_arg = func_get_args();

    if (func_num_args() == 2)
    {
        return '<style type="text/css" ' . _attr($a_arg[0]) . '>' . $a_arg[1] . '</style>';
    }

    return '<style type="text/css">' . $a_arg[0] . '</style>';
}

function _css()
{
    $a_arg = func_get_args();

    if (func_num_args() == 2)
    {
        return '<link rel="stylesheet" href="' . $a_arg[1] . '" ' . _attr($a_arg[0]) . ' />';
    }

    return '<link rel="stylesheet" href="' . $a_arg[0] . '" />';
}

function _js()
{
    $a_arg = func_get_args();

    if (func_num_args() == 2)
    {
        return '<script src="' . $a_arg[1] . '" ' . _attr($a_arg[0]) . '></script>';
    }

    return '<script src="' . $a_arg[0] . '"></script>';
}

function _comp($cond, $data)
{
    return '<!--[if ' . $cond . ']>' . $data . '<![endif]-->';
}

function _gly()
{
    $a_arg = func_get_args();

    if (func_num_args() == 2)
    {
        return '<span class="glyphicon ' . $a_arg[1] . '" ' . _attr($a_arg[0]) . '></span>';
    }

    return '<span class="glyphicon ' . $a_arg[0] . '"></span>';
}

function _fa($class, $attr = null)
{
    $a_arg = func_get_args();

    if (func_num_args() == 2)
    {
        return '<i class="fa ' . $a_arg[1] . '" ' . _attr($a_arg[0]) . '></i>';
    }

    return '<i class="fa ' . $a_arg[0] . '"></i>';
}

function _mdi($class, $attr = null)
{
    $a_arg = func_get_args();

    if (func_num_args() == 2)
    {
        return '<i class="mdi ' . $a_arg[1] . '" ' . _attr($a_arg[0]) . '></i>';
    }

    return '<i class="mdi ' . $a_arg[0] . '"></i>';
}

function _comment($comment, $data)
{
    return "<!-- $comment -->$data<!-- /$comment -->";
}

function _nbsp($nb)
{
    return str_repeat("&nbsp;", $nb);
}

// Debug

function _pr($data)
{
    return "<pre>" . (print_r($data, true)) . "</pre>";
}

function _prh($data)
{
    return "<pre>" . htmlentities(print_r($data, true)) . "</pre>";
}

function _vd($data)
{
    ob_start();
    var_dump($data);
    return "<pre>" . (ob_get_clean()) . "</pre>";
}

function _vdh($data)
{
    ob_start();
    var_dump($data);
    return "<pre>" . htmlentities(ob_get_clean()) . "</pre>";
}

function _ve($data)
{
    return "<pre>" . (var_export($data, true)) . "</pre>";
}

function _veh($data)
{
    return "<pre>" . htmlentities(var_export($data, true)) . "</pre>";
}

// others

function _include($path)
{
    if (file_exists($path))
    {
        ob_start();
        include $path;
        return ob_get_clean();
    }
}

function _indent($data, $t = -1)
{
    $a_data = explode("\n", preg_replace("%(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+%", "\n", preg_replace(array("%(<[^>]+>)%", "%(</[^>]+>)%"), array("\${1}\n", "\n\${1}"), $data)));

    $flag = true;
    foreach ($a_data as &$v)
    {
        if ($flag == true)
        {
            if (preg_match("%^<!--\[%", $v))
            {
                $v = str_repeat("\t", $t < 0 ? 0 : $t) . trim($v);

                $t++;
            }
            elseif (preg_match("%^<!\[%", $v))
            {
                $t--;

                $v = str_repeat("\t", $t < 0 ? 0 : $t) . trim($v);
            }
            elseif (preg_match("%^<[^>]*/>$%", $v) or preg_match("%^<!%", $v))
            {
                $v = str_repeat("\t", $t < 0 ? 0 : $t) . trim($v);
            }
            elseif (preg_match("%^<[^/]%", $v))
            {
                $v = str_repeat("\t", $t < 0 ? 0 : $t) . trim($v);

                $t++;
            }
            elseif (preg_match("%^</[^>]*>$%", $v))
            {
                $t--;

                $v = str_repeat("\t", $t < 0 ? 0 : $t) . trim($v);
            }
            else
            {
                $v = str_repeat("\t", $t < 0 ? 0 : $t) . trim($v);
            }
        }

        if (preg_match("%</pre%", $v))
        {
            $flag = true;
        }
        elseif (preg_match("%<pre%", $v))
        {
            $flag = false;
        }
    }

    foreach ($a_data as $k => $v)
    {
        if (!preg_match("%<!%", $v) and !preg_match("%/>%", $v) and preg_match("%<[^/]%", $v) and isset($a_data[$k + 1]) and preg_match("%</%", $a_data[$k + 1]))
        {
            $a_data[$k] = $a_data[$k] . trim($a_data[$k + 1]);

            unset($a_data[$k + 1]);
        }
    }

    array_pop($a_data);

    return implode("\n", $a_data);
}
