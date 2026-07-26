<?php
if (!defined('CH_APP')) { http_response_code(403); exit; }

return [
  'estate-sale' => [
    'label' => 'Estate Sale & Cleanout',
    'short' => 'Estate Sale & Cleanout',
    'color' => 'var(--navy)',
    'blurb' => "Companies that appraise, price, stage, and sell the contents of a home — then clear what's left — for downsizing, moving, or settling an estate.",
  ],
  'junk-removal' => [
    'label' => 'Junk Removal',
    'short' => 'Junk Removal',
    'color' => 'var(--sage)',
    'blurb' => 'Hauling crews that physically remove furniture, trash, and debris — the muscle for a clean-out, without appraising or selling contents.',
  ],
  'hoarding-biohazard' => [
    'label' => 'Hoarding & Biohazard Cleanup',
    'short' => 'Hoarding & Biohazard',
    'color' => 'var(--rust)',
    'blurb' => 'Specialized crews trained for extreme clutter, contamination, or biohazard situations that go beyond a standard clean-out.',
  ],
];
