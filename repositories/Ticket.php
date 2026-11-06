<?php

namespace App\Repositories;

class Ticket extends Model
{
    protected static string $table = 'tickets';
    protected static array $fillable = [
        'order_id', 'order_item_id', 'ticket_code', 'seat_label', 'qr_path',
        'holder_name', 'holder_email', 'is_checked_in', 'checked_in_at', 'is_used'
    ];
    
    public function order(): ?Order
    {
        return Order::find($this->order_id);
    }
    
    public function orderItem(): ?OrderItem
    {
        return OrderItem::find($this->order_item_id);
    }
    
    public function generateQR(): string
    {
        return $this->ticket_code;
    }

    public function qrDataUrl(int $size = 220): string
    {
        $code = $this->ticket_code ?? 'ATX-UNKNOWN';
        // Try GD generation: produce a QR-like pattern derived from hash, not a real QR but unique per ticket
        if (extension_loaded('gd') && function_exists('imagecreatetruecolor')) {
            $modules = 21; // QR version 1 size
            $pixel = max(4, intdiv($size, $modules));
            $imgSize = $modules * $pixel;
            $img = imagecreatetruecolor($imgSize, $imgSize);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefill($img, 0, 0, $white);

            $hash = md5($code);
            // Draw position patterns (corners)
            $drawFinder = function(int $ox, int $oy) use ($img, $black, $white, $pixel) {
                // 7x7 finder
                imagefilledrectangle($img, $ox*$pixel, $oy*$pixel, ($ox+7)*$pixel-1, ($oy+7)*$pixel-1, $black);
                imagefilledrectangle($img, ($ox+1)*$pixel, ($oy+1)*$pixel, ($ox+6)*$pixel-1, ($oy+6)*$pixel-1, $white);
                imagefilledrectangle($img, ($ox+2)*$pixel, ($oy+2)*$pixel, ($ox+5)*$pixel-1, ($oy+5)*$pixel-1, $black);
            };
            $drawFinder(0,0);
            $drawFinder($modules-7,0);
            $drawFinder(0,$modules-7);

            // Fill data area using hash bits
            $hashBits = '';
            foreach (str_split($hash) as $c) {
                $hashBits .= str_pad(base_convert($c, 16, 2), 4, '0', STR_PAD_LEFT);
            }
            $hashBits = str_repeat($hashBits, 4); // expand
            $bitIdx = 0;
            for ($y=0; $y<$modules; $y++) {
                for ($x=0; $x<$modules; $x++) {
                    // skip finder + separator
                    $inFinder = ($x<7 && $y<7) || ($x>=$modules-7 && $y<7) || ($x<7 && $y>=$modules-7);
                    if ($inFinder) continue;
                    // timing pattern etc simplified: leave 1px border
                    if ($x===6 || $y===6) {
                        $on = (($x+$y)%2===0);
                        if ($on) imagefilledrectangle($img, $x*$pixel, $y*$pixel, ($x+1)*$pixel-1, ($y+1)*$pixel-1, $black);
                        continue;
                    }
                    $on = ($hashBits[$bitIdx % strlen($hashBits)] === '1');
                    $bitIdx++;
                    // add some randomness for visual QR feel but deterministic
                    if ($on) imagefilledrectangle($img, $x*$pixel, $y*$pixel, ($x+1)*$pixel-1, ($y+1)*$pixel-1, $black);
                }
            }

            ob_start();
            imagepng($img);
            $png = ob_get_clean();
            imagedestroy($img);
            return 'data:image/png;base64,' . base64_encode($png);
        }
        // Fallback: generate simple SVG with code as text (still printable)
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 120 120"><rect width="120" height="120" fill="white" stroke="#e2e8f0"/><text x="60" y="55" font-family="monospace" font-size="7" text-anchor="middle" fill="#1e293b">'.h($code).'</text><text x="60" y="70" font-family="sans-serif" font-size="6" text-anchor="middle" fill="#64748b">Tunjukkan di pintu masuk</text></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}