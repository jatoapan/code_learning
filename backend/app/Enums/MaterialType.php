<?php

namespace App\Enums;

enum MaterialType: string
{
    case Pdf = 'pdf';
    case VideoLink = 'video_link';
    case Ppt = 'ppt';
    case Pptx = 'pptx';
}
