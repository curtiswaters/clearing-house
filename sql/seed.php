<?php
/**
 * One-time data import: populates the businesses table from the original
 * hard-coded listing data. Safe to re-run — new rows are inserted, and for
 * rows that already exist, only the `website` column is refreshed (any
 * other edits made since, e.g. through /admin/, are left alone).
 *
 * Usage: php sql/seed.php   (from a terminal with DB access), or visit
 * this file once in a browser, then delete it or move it out of public_html.
 */

require __DIR__ . '/../api/db.php';

$businesses = [
  ['caring-transitions-sw', 'Caring Transitions of Charlotte South & West', 'estate-sale', '(704) 321-4000', 'Charlotte, NC', 'caringtransitionsofcharlottesw.com', 'Full-service senior relocation, downsizing, and online-auction estate sales.', "Coordinates the whole transition — from sorting and appraising belongings to running an in-home or online (CTBids) estate sale — for families downsizing, relocating, or settling an estate."],
  ['estate-buyers-liquidators', 'Estate Buyers and Liquidators', 'estate-sale', '(980) 446-4849', 'Charlotte, NC', 'estateliquidatorscharlotte.com', 'Buys and liquidates full estate contents in one visit.', 'Evaluates and purchases estate contents directly, offering a faster alternative to running a multi-day public sale.'],
  ['mike-d-antiques', 'Mike D Antiques & Estate Planning', 'estate-sale', '(704) 968-2613', 'Gastonia, NC', null, 'Family-run estate sale company known for fair, first-come pricing.', 'Runs in-home estate sales with a relaxed, honor-system approach to pricing, and also operates an antiques shop for consigned pieces.'],
  ['auctions-on-demand', 'Auctions On Demand LLC', 'estate-sale', '(803) 325-7580', 'Rock Hill, SC', 'auctionsondemand.com', 'Online auction house for estate and liquidation items.', 'Photographs, lists, and auctions estate items online with in-person inspection before bidding, an alternative to a traditional in-home sale.'],
  ['bluestar-move-management', 'Bluestar Move Management', 'estate-sale', '(980) 938-2490', 'Charlotte, NC', 'bluestarmoving.com/estate-sales', 'Plans and executes senior and downsizing moves start to finish.', 'Handles floor planning, packing, and unpacking for downsizing moves, coordinating what stays, sells, donates, or gets hauled away.'],
  ['charlotte-sisters-organizing', 'Charlotte Sisters Organizing LLC', 'estate-sale', '(813) 495-4944', 'Charlotte, NC', 'cltsos.com', 'Home organizing team that helps sort and purge before a move or sale.', 'Works alongside families to sort, organize, and purge belongings — often paired with an estate sale or move — to get a home ready to list or downsize into.'],
  ['fresh-start-moving', 'Fresh Start Moving Services', 'estate-sale', '(704) 241-2147', 'Gastonia, NC', 'freshstartmovers.com', "Moving company that also clears out leftover items after a move.", "Primarily a residential moving crew, with add-on junk and furniture removal for whatever doesn't make the trip to the new home."],

  ['carolina-junk-out', 'Carolina Junk Out', 'junk-removal', '(980) 428-9057', 'Charlotte, NC', 'carolinajunkout.com', 'Fast, full-property junk hauling for move-outs.', 'Two-person crew handling whole-property hauls, from single items to full house clear-outs, with same-day turnaround in many cases.'],
  ['gh-junk-removal', 'GH Junk Removal and Cleaning Services', 'junk-removal', '(980) 680-3793', 'Charlotte, NC', 'ncjunkremovalsvc.com', 'Same-day furniture and junk pickup.', 'Removes furniture, appliances, and general junk with quick response times, often arriving within the hour of a call.'],
  ['two-men-junk-truck', 'Two Men and a Junk Truck', 'junk-removal', '(704) 981-6264', 'Charlotte, NC', 'twomenandajunktruck.com/locations/nc/junk-removal-charlotte', 'Locally run hauling crew for furniture and clutter.', 'Handles residential junk removal jobs of all sizes, from single-item pickups to clearing out an entire house.'],
  ['junk-raider', 'Junk Raider', 'junk-removal', '(704) 594-1271', 'Charlotte, NC', 'junkraider.com', 'Sorts what stays and what goes before hauling.', 'Clears sheds, garages, and full homes, checking in on what to keep versus discard rather than hauling everything indiscriminately.'],
  ['ca-junk-removal', 'C&A Junk Removal and Demolition LLC', 'junk-removal', '(980) 285-0564', 'Charlotte, NC', null, 'Handles heavy and oversized items, including pianos.', 'Takes on full-property haul-outs, including large or awkward items like pianos and appliances, with flexible same-week scheduling.'],
  ['junk-rescue', 'Junk Rescue', 'junk-removal', '(800) 586-5911', 'Charlotte, NC', 'junkrescue.com', 'Donates reusable items instead of sending everything to landfill.', 'Clears full properties while sorting out items with remaining useful life for donation, reducing what ends up in a landfill.'],
  ['trash-and-stash', 'Trash and Stash Junk Removal', 'junk-removal', '(704) 327-2446', 'Charlotte, NC', 'trashandstash.com', 'Property management-grade cleanouts with donation coordination.', 'Regularly used for property management and estate cleanouts, coordinating donation drop-offs alongside standard hauling.'],
  ['lilly-n-me', "Lilly n' Me Junk Removal and Dumpster Rental", 'junk-removal', '(704) 877-1298', 'Charlotte, NC', 'lillyandmejunkremoval.com', 'Junk hauling plus on-site dumpster rental.', 'Offers both hands-on junk removal and dumpster drop-off, useful for larger clean-out jobs done over several days.'],
  ['junk-king-charlotte', 'Junk King Charlotte', 'junk-removal', '(704) 469-4815', 'Charlotte, NC', 'junk-king.com/locations/charlotte', 'National franchise crew for full-home and yard clear-outs.', 'Removes furniture, sheds, and yard debris as part of a national franchise network, with upfront pricing before work begins.'],
  ['college-hunks', 'College Hunks Hauling Junk and Moving', 'junk-removal', '(704) 286-9321', 'Charlotte, NC', 'collegehunkshaulingjunk.com/charlotte', 'Combines moving labor with junk hauling in one crew.', 'Pairs moving-day labor with junk removal, useful when a clean-out and a move are happening at the same time.'],

  ['toss-it-hoarding', 'Toss It Hoarding Cleanup Services', 'hoarding-biohazard', '(704) 313-1301', 'Charlotte, NC', null, 'Clears heavily cluttered or hoarded homes quickly.', 'Specializes in fast turnarounds on hoarded or heavily cluttered properties, often ahead of a sale or listing deadline.'],
  ['load-it-biohazard', 'Load It Biohazard Cleanup Services', 'hoarding-biohazard', '(704) 251-6372', 'Charlotte, NC', 'loaditjunk.com', 'Handles biohazard and contamination cleanup.', 'Addresses biohazard and contamination issues that sometimes accompany an estate situation, beyond standard cleaning or hauling.'],
  ['combat-cleaning', 'Combat Cleaning — Carpet, Biohazard, Steam & Hoarding', 'hoarding-biohazard', '(980) 445-8443', 'Cornelius, NC', 'combatcleaningclt.com', 'Deep cleaning and hoarding remediation for lake-area homes.', 'Provides deep carpet and steam cleaning alongside hoarding remediation, serving the Lake Norman area north of Charlotte.'],
];

$stmt = $pdo->prepare(
  'INSERT INTO businesses (id, name, category, phone, city, website, oneliner, description, featured)
   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
   ON DUPLICATE KEY UPDATE website = VALUES(website)'
);

foreach ($businesses as $b) {
  $stmt->execute($b);
}

echo "Done. Processed " . count($businesses) . " row(s).\n";
