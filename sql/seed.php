<?php
/**
 * One-time data import: populates the businesses table from the original
 * hard-coded listing data. Safe to re-run — new rows are inserted, and for
 * rows that already exist, only the `website` column is refreshed (any
 * other edits made since, e.g. through /admin/, are left alone — including
 * `markets`, which only gets set on first insert).
 *
 * Usage: php sql/seed.php   (from a terminal with DB access), or visit
 * this file once in a browser, then delete it or move it out of public_html.
 */

require __DIR__ . '/../api/db.php';

$businesses = [
  ['caring-transitions-sw', 'Caring Transitions of Charlotte South & West', 'estate-sale', '(704) 321-4000', 'Charlotte, NC', 'caringtransitionsofcharlottesw.com', 'Full-service senior relocation, downsizing, and online-auction estate sales.', "Coordinates the whole transition — from sorting and appraising belongings to running an in-home or online (CTBids) estate sale — for families downsizing, relocating, or settling an estate.", 'charlotte-nc'],
  ['estate-buyers-liquidators', 'Estate Buyers and Liquidators', 'estate-sale', '(980) 446-4849', 'Charlotte, NC', 'estateliquidatorscharlotte.com', 'Buys and liquidates full estate contents in one visit.', 'Evaluates and purchases estate contents directly, offering a faster alternative to running a multi-day public sale.', 'charlotte-nc'],
  ['mike-d-antiques', 'Mike D Antiques & Estate Planning', 'estate-sale', '(704) 968-2613', 'Gastonia, NC', null, 'Family-run estate sale company known for fair, first-come pricing.', 'Runs in-home estate sales with a relaxed, honor-system approach to pricing, and also operates an antiques shop for consigned pieces.', 'gastonia-nc'],
  ['auctions-on-demand', 'Auctions On Demand LLC', 'estate-sale', '(803) 325-7580', 'Rock Hill, SC', 'auctionsondemand.com', 'Online auction house for estate and liquidation items.', 'Photographs, lists, and auctions estate items online with in-person inspection before bidding, an alternative to a traditional in-home sale.', 'rock-hill-sc'],
  ['bluestar-move-management', 'Bluestar Move Management', 'estate-sale', '(980) 938-2490', 'Charlotte, NC', 'bluestarmoving.com/estate-sales', 'Plans and executes senior and downsizing moves start to finish.', 'Handles floor planning, packing, and unpacking for downsizing moves, coordinating what stays, sells, donates, or gets hauled away.', 'charlotte-nc'],
  ['charlotte-sisters-organizing', 'Charlotte Sisters Organizing LLC', 'estate-sale', '(813) 495-4944', 'Charlotte, NC', 'cltsos.com', 'Home organizing team that helps sort and purge before a move or sale.', 'Works alongside families to sort, organize, and purge belongings — often paired with an estate sale or move — to get a home ready to list or downsize into.', 'charlotte-nc'],
  ['fresh-start-moving', 'Fresh Start Moving Services', 'estate-sale', '(704) 241-2147', 'Gastonia, NC', 'freshstartmovers.com', "Moving company that also clears out leftover items after a move.", "Primarily a residential moving crew, with add-on junk and furniture removal for whatever doesn't make the trip to the new home.", 'gastonia-nc'],
  ['one-community-auctions', 'One Community Real Estate and Auction', 'estate-sale', '(704) 507-5500', 'Concord, NC', 'onecommunityauctions.com', 'Runs household estate auctions alongside the home sale itself.', 'Pairs personal-property auctions — furniture, collectibles, vehicles — with the sale of the house itself, so downsizing and estate-settlement families can wrap up the home and its contents under one plan.', 'concord-nc'],
  ['caring-transitions-fort-mill', 'Caring Transitions of Fort Mill, SC', 'estate-sale', '(803) 455-6410', 'Fort Mill, SC', 'caringtransitionsoffortmillsc.com', 'Full-service estate sales and online auctions for Fort Mill families.', 'Coordinates in-home and online (CTBids-style) estate sales, downsizing, and relocation moves for families settling an estate or moving in the Fort Mill area.', 'fort-mill-sc'],
  ['queen-city-estate-sales', 'Queen City Estate Sales', 'estate-sale', '(704) 281-7069', 'Charlotte, NC', 'queencityestate.com', 'Charlotte-area estate sale company with 20+ years serving the region.', 'Runs in-home estate sales across the wider Charlotte area, including Indian Land, with an emphasis on minimizing stress for families while maximizing returns.', 'charlotte-nc,indian-land-sc'],

  ['carolina-junk-out', 'Carolina Junk Out', 'junk-removal', '(980) 428-9057', 'Charlotte, NC', 'carolinajunkout.com', 'Fast, full-property junk hauling for move-outs.', 'Two-person crew handling whole-property hauls, from single items to full house clear-outs, with same-day turnaround in many cases.', 'charlotte-nc'],
  ['gh-junk-removal', 'GH Junk Removal and Cleaning Services', 'junk-removal', '(980) 680-3793', 'Charlotte, NC', 'ncjunkremovalsvc.com', 'Same-day furniture and junk pickup.', 'Removes furniture, appliances, and general junk with quick response times, often arriving within the hour of a call.', 'charlotte-nc'],
  ['two-men-junk-truck', 'Two Men and a Junk Truck', 'junk-removal', '(704) 981-6264', 'Charlotte, NC', 'twomenandajunktruck.com/locations/nc/junk-removal-charlotte', 'Locally run hauling crew for furniture and clutter.', 'Handles residential junk removal jobs of all sizes, from single-item pickups to clearing out an entire house.', 'charlotte-nc'],
  ['junk-raider', 'Junk Raider', 'junk-removal', '(704) 594-1271', 'Charlotte, NC', 'junkraider.com', 'Sorts what stays and what goes before hauling.', 'Clears sheds, garages, and full homes, checking in on what to keep versus discard rather than hauling everything indiscriminately.', 'charlotte-nc'],
  ['ca-junk-removal', 'C&A Junk Removal and Demolition LLC', 'junk-removal', '(980) 285-0564', 'Charlotte, NC', null, 'Handles heavy and oversized items, including pianos.', 'Takes on full-property haul-outs, including large or awkward items like pianos and appliances, with flexible same-week scheduling.', 'charlotte-nc'],
  ['junk-rescue', 'Junk Rescue', 'junk-removal', '(800) 586-5911', 'Charlotte, NC', 'junkrescue.com', 'Donates reusable items instead of sending everything to landfill.', 'Clears full properties while sorting out items with remaining useful life for donation, reducing what ends up in a landfill.', 'charlotte-nc'],
  ['trash-and-stash', 'Trash and Stash Junk Removal', 'junk-removal', '(704) 327-2446', 'Charlotte, NC', 'trashandstash.com', 'Property management-grade cleanouts with donation coordination.', 'Regularly used for property management and estate cleanouts, coordinating donation drop-offs alongside standard hauling.', 'charlotte-nc'],
  ['lilly-n-me', "Lilly n' Me Junk Removal and Dumpster Rental", 'junk-removal', '(704) 877-1298', 'Charlotte, NC', 'lillyandmejunkremoval.com', 'Junk hauling plus on-site dumpster rental.', 'Offers both hands-on junk removal and dumpster drop-off, useful for larger clean-out jobs done over several days.', 'charlotte-nc'],
  ['junk-king-charlotte', 'Junk King Charlotte', 'junk-removal', '(704) 469-4815', 'Charlotte, NC', 'junk-king.com/locations/charlotte', 'National franchise crew for full-home and yard clear-outs.', 'Removes furniture, sheds, and yard debris as part of a national franchise network, with upfront pricing before work begins.', 'charlotte-nc'],
  ['college-hunks', 'College Hunks Hauling Junk and Moving', 'junk-removal', '(704) 286-9321', 'Charlotte, NC', 'collegehunkshaulingjunk.com/charlotte', 'Combines moving labor with junk hauling in one crew.', 'Pairs moving-day labor with junk removal, useful when a clean-out and a move are happening at the same time.', 'charlotte-nc'],
  ['blessed-moving', 'Blessed Moving', 'junk-removal', '(704) 648-6775', 'Concord, NC', 'blessedmoving.com', 'Moving crew that also hauls away what does not make the move.', "Primarily a residential and commercial moving company, offering household junk removal alongside packing and relocation for whatever gets left behind.", 'concord-nc'],

  ['toss-it-hoarding', 'Toss It Hoarding Cleanup Services', 'hoarding-biohazard', '(704) 313-1301', 'Charlotte, NC', null, 'Clears heavily cluttered or hoarded homes quickly.', 'Specializes in fast turnarounds on hoarded or heavily cluttered properties, often ahead of a sale or listing deadline.', 'charlotte-nc'],
  ['load-it-biohazard', 'Load It Biohazard Cleanup Services', 'hoarding-biohazard', '(704) 251-6372', 'Charlotte, NC', 'loaditjunk.com', 'Handles biohazard and contamination cleanup.', 'Addresses biohazard and contamination issues that sometimes accompany an estate situation, beyond standard cleaning or hauling.', 'charlotte-nc'],
  ['combat-cleaning', 'Combat Cleaning — Carpet, Biohazard, Steam & Hoarding', 'hoarding-biohazard', '(980) 445-8443', 'Cornelius, NC', 'combatcleaningclt.com', 'Deep cleaning and hoarding remediation for lake-area homes.', 'Provides deep carpet and steam cleaning alongside hoarding remediation, serving the Lake Norman area north of Charlotte.', 'cornelius-nc'],
  ['bio-one-charlotte', 'Bio-One of Charlotte', 'hoarding-biohazard', '(704) 726-5905', 'Concord, NC', 'biooneinc.com/charlotte-nc/concord', 'Compassionate hoarding and biohazard cleanup, 24/7.', 'Handles hoarding cleanup, biohazard and crime-scene decontamination, and trauma-related cleaning for Concord-area homes, with 24/7 response.', 'concord-nc'],
  ['puroclean-fort-mill', 'PuroClean of Fort Mill', 'hoarding-biohazard', '(803) 650-3810', 'Fort Mill, SC', 'puroclean.com/ft-mill-sc-puroclean-fort-mill', 'Restoration company that also handles biohazard and hoarding cleanup.', 'Primarily a fire, water, and mold restoration company, with biohazard and crime-scene cleanup and hoarding remediation offered alongside 24/7 emergency response.', 'fort-mill-sc'],
  ['servpro-indian-land', 'SERVPRO of Indian Land, Cherokee, Union and Chester Counties', 'hoarding-biohazard', '(803) 581-1570', 'Indian Land, SC', 'servpro.com/locations/sc/servpro-of-indian-land-cherokee-union-and-chester-counties', 'Local SERVPRO franchise handling biohazard and hoarding cleanup.', 'Provides 24/7 biohazard, crime-scene, and hoarding cleanup for Indian Land and the surrounding Lancaster County area, as part of the national SERVPRO network.', 'indian-land-sc'],
];

$stmt = $pdo->prepare(
  'INSERT INTO businesses (id, name, category, phone, city, website, oneliner, description, markets, featured)
   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
   ON DUPLICATE KEY UPDATE website = VALUES(website)'
);

foreach ($businesses as $b) {
  $stmt->execute($b);
}

echo "Done. Processed " . count($businesses) . " row(s).\n";
