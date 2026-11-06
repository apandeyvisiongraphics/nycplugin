<?php
if (!defined('ABSPATH')) { http_response_code(404); exit; }
return json_decode(<<<'VGNYC_MANIFEST'
{
  "/nyc/about/": {
    "title": "Vision Graphics for your New York business | Vision Graphics New York",
    "description": "Vision Graphics’ New York experience brings a local conversation together with VG production support.",
    "file": "about.php",
    "template": "service",
    "content": "content/services/about.json",
    "service": false,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp",
    "draft_template": "landing",
    "draft_content": "content/pages/about.json",
    "draft_title": "About Vision Graphics New York | NYC Sign Company",
    "draft_description": "Learn who Vision Graphics is, where we make your NYC signs, and who installs them. A 60-year-old visual communications company. Send a photo for a quote.",
    "draft_image": "nyc-about-hero-01"
  },
  "/nyc/acrylic-signs/": {
    "title": "Acrylic signs with a clear design purpose | Vision Graphics New York",
    "description": "Acrylic offers several ways to present a business identity, from a simple wall panel to layered dimensional elements.",
    "file": "acrylic-signs.php",
    "template": "service",
    "content": "content/services/acrylic-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-acrylic-signs-960.webp"
  },
  "/nyc/acrylic-sneeze-guards/": {
    "title": "Acrylic barrier concepts for counters | Vision Graphics New York",
    "description": "An acrylic counter barrier is a physical screen that must fit the way staff and customers use the space.",
    "file": "acrylic-sneeze-guards.php",
    "template": "service",
    "content": "content/services/acrylic-sneeze-guards.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-hero-image-960.webp"
  },
  "/nyc/ada-signs/": {
    "title": "Tactile & ADA Sign Planning in NYC | Vision Graphics",
    "description": "Plan tactile room signs, restroom identification and coordinated signage for New York properties. Prepare your sign schedule, artwork and installation scope.",
    "file": "ada-signs.php",
    "template": "service",
    "content": "content/services/ada-signs.json",
    "service": true,
    "image": "/nyc/assets/vg-wayfinding-ada-960.webp"
  },
  "/nyc/apparel/": {
    "title": "Branded apparel project planning | Vision Graphics New York",
    "description": "Branded apparel can connect the appearance of a team with the graphics on its vehicles, events and workplace.",
    "file": "apparel.php",
    "template": "service",
    "content": "content/services/apparel.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/banners/": {
    "title": "Custom banners for a focused message | Vision Graphics New York",
    "description": "A banner gives a campaign, event or announcement a visible temporary home.",
    "file": "banners.php",
    "template": "service",
    "content": "content/services/banners.json",
    "service": true,
    "image": "/nyc/assets/mcs-banners-960.webp"
  },
  "/nyc/blade-signs/": {
    "title": "Blade sign concepts for the pedestrian approach | Vision Graphics New York",
    "description": "A blade sign projects from a building so the business identity can face people moving along the street.",
    "file": "blade-signs.php",
    "template": "service",
    "content": "content/services/blade-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-building-sign-type-blade-hanging-signs-960.webp"
  },
  "/nyc/brooklyn/": {
    "title": "Custom Signs & Graphics for Brooklyn | Vision Graphics",
    "description": "Plan storefront signs, window graphics and office branding for Brooklyn, including Williamsburg and DUMBO. Share photos to review your project scope.",
    "file": "brooklyn.php",
    "template": "service",
    "content": "content/services/brooklyn.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-building-signage-storefronts-960.webp"
  },
  "/nyc/building-signs/": {
    "title": "Building identification with the site in mind | Vision Graphics New York",
    "description": "A building sign connects an organization to a physical place.",
    "file": "building-signs.php",
    "template": "service",
    "content": "content/services/building-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-home-feature-960.webp"
  },
  "/nyc/business-signs/": {
    "title": "Business signs for the complete customer journey | Vision Graphics New York",
    "description": "Business signage works best when each piece answers a real question: Is this the right place?",
    "file": "business-signs.php",
    "template": "service",
    "content": "content/services/business-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/cabinet-signs/": {
    "title": "Cabinet sign planning for business identity | Vision Graphics New York",
    "description": "A cabinet sign places the message within a single enclosure, making the sign face a defined canvas for a name, logo or coordinated tenant identity.",
    "file": "cabinet-signs.php",
    "template": "service",
    "content": "content/services/cabinet-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-exterior-signs-960.webp"
  },
  "/nyc/channel-letter-signs/": {
    "title": "Channel letter signs: form, light and placement | Vision Graphics New York",
    "description": "Channel letters use individually formed characters to create a strong business identity, often with illumination as part of the design.",
    "file": "channel-letter-signs.php",
    "template": "service",
    "content": "content/services/channel-letter-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-hero-channel-960.webp"
  },
  "/nyc/contact/": {
    "title": "Tell us about your New York project | Vision Graphics New York",
    "description": "A useful signage conversation can start with a simple description of what you need to change.",
    "file": "contact.php",
    "template": "raw",
    "content": "pages/contact.php",
    "service": false,
    "image": "/nyc/assets/vg-logo-dark.webp",
    "draft_template": "landing",
    "draft_content": "content/pages/contact.json",
    "draft_title": "Request a Sign Quote in NYC | Vision Graphics NYC",
    "draft_description": "Send photos and a few details about your NYC sign project. A Vision Graphics project lead replies within one business day with next steps and a quote."
  },
  "/nyc/corrugated-signage/": {
    "title": "Corrugated signs for planned short-term use | Vision Graphics New York",
    "description": "Corrugated plastic signs can be a practical format for temporary messages, events and property marketing when the application suits a lightweight panel.",
    "file": "corrugated-signage.php",
    "template": "service",
    "content": "content/services/corrugated-signage.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/custom-decals/": {
    "title": "Custom decals for consistent brand details | Vision Graphics New York",
    "description": "A decal is a small graphic with a specific job, whether it identifies equipment, carries a business logo or marks a promotional item.",
    "file": "custom-decals.php",
    "template": "service",
    "content": "content/services/custom-decals.json",
    "service": true,
    "image": "/nyc/assets/mcs-custom-decals-960.webp"
  },
  "/nyc/custom-signs/": {
    "title": "Custom signs shaped around the job | Vision Graphics New York",
    "description": "A custom sign starts with a communication problem, not a predetermined material.",
    "file": "custom-signs.php",
    "template": "service",
    "content": "content/services/custom-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/digital-signs/": {
    "title": "Digital sign project planning | Vision Graphics New York",
    "description": "Digital signage introduces a changing content system rather than a single printed message.",
    "file": "digital-signs.php",
    "template": "service",
    "content": "content/services/digital-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/dimensional-letter-signs/": {
    "title": "Dimensional Letters & Logo Signs NYC | Vision Graphics",
    "description": "Explore dimensional letters and logo signs for New York lobbies and businesses. Compare materials, depth, mounting and quote requirements with Vision Graphics.",
    "file": "dimensional-letter-signs.php",
    "template": "service",
    "content": "content/services/dimensional-letter-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-dimensional-lobby-letters-960.webp"
  },
  "/nyc/directional-signs/": {
    "title": "Directional signs at the moment of decision | Vision Graphics New York",
    "description": "Directional signs are most useful where someone must choose a route.",
    "file": "directional-signs.php",
    "template": "service",
    "content": "content/services/directional-signs.json",
    "service": true,
    "image": "/nyc/assets/vg-wayfinding-ada-960.webp"
  },
  "/nyc/door-signs/": {
    "title": "Door signs that identify the destination | Vision Graphics New York",
    "description": "A door sign is the final confirmation that someone has reached the right place.",
    "file": "door-signs.php",
    "template": "service",
    "content": "content/services/door-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/environmental-graphics/": {
    "title": "Environmental graphics for a connected place | Vision Graphics New York",
    "description": "Environmental graphics bring branding, storytelling and useful information into the physical space.",
    "file": "environmental-graphics.php",
    "template": "service",
    "content": "content/services/environmental-graphics.json",
    "service": true,
    "image": "/nyc/assets/mcs-interior-signs-960.webp"
  },
  "/nyc/exterior-signs/": {
    "title": "Exterior signs for a recognizable business | Vision Graphics New York",
    "description": "An exterior sign has to work in a busy setting.",
    "file": "exterior-signs.php",
    "template": "service",
    "content": "content/services/exterior-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-exterior-signs-960.webp"
  },
  "/nyc/flags/": {
    "title": "Branded flags for event and display planning | Vision Graphics New York",
    "description": "A branded flag introduces movement and a tall visual element into an event or promotional setting.",
    "file": "flags.php",
    "template": "service",
    "content": "content/services/flags.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-building-sign-type-banners-flags-960.webp"
  },
  "/nyc/fleet-wraps/": {
    "title": "Fleet graphics with consistent decisions | Vision Graphics New York",
    "description": "A fleet should look connected even when the vehicles are not identical.",
    "file": "fleet-wraps.php",
    "template": "service",
    "content": "content/services/fleet-wraps.json",
    "service": true,
    "image": "/nyc/assets/mcs-fleet-wraps-960.webp"
  },
  "/nyc/floor-graphics/": {
    "title": "Floor graphics with a defined purpose | Vision Graphics New York",
    "description": "Floor graphics put a message into the path people use, which makes placement and material suitability essential.",
    "file": "floor-graphics.php",
    "template": "service",
    "content": "content/services/floor-graphics.json",
    "service": true,
    "image": "/nyc/assets/mcs-interior-signs-960.webp"
  },
  "/nyc/gallery/custom-signs/": {
    "title": "Custom sign design inspiration | Vision Graphics New York",
    "description": "Custom sign examples show how a particular message can be expressed through shape, material and placement.",
    "file": "gallery--custom-signs.php",
    "template": "raw",
    "content": "pages/gallery--custom-signs.php",
    "service": false,
    "image": "/nyc/assets/mcs-window-graphics-960.webp"
  },
  "/nyc/gallery/exterior-signs/": {
    "title": "Exterior sign inspiration | Vision Graphics New York",
    "description": "Exterior sign photographs show how a business identity sits within a larger streetscape.",
    "file": "gallery--exterior-signs.php",
    "template": "raw",
    "content": "pages/gallery--exterior-signs.php",
    "service": false,
    "image": "/nyc/assets/mcs-window-graphics-960.webp"
  },
  "/nyc/gallery/": {
    "title": "Sign and graphics project inspiration | Vision Graphics New York",
    "description": "Explore visual approaches to business signs, interiors, vehicles and event displays, then use the examples to sharpen your own project brief.",
    "file": "gallery.php",
    "template": "raw",
    "content": "pages/gallery.php",
    "service": false,
    "image": "/nyc/assets/mcs-window-graphics-960.webp"
  },
  "/nyc/gallery/interior-signs/": {
    "title": "Interior sign inspiration | Vision Graphics New York",
    "description": "Interior sign examples are most useful when you look at how the graphics relate to the room.",
    "file": "gallery--interior-signs.php",
    "template": "raw",
    "content": "pages/gallery--interior-signs.php",
    "service": false,
    "image": "/nyc/assets/mcs-new-hero-image-960.webp"
  },
  "/nyc/gallery/trade-show-graphics/": {
    "title": "Trade show and display inspiration | Vision Graphics New York",
    "description": "Event graphics are easiest to assess as a group: the backdrop introduces the brand, portable displays support a message and close-range materials help a conversation continue.",
    "file": "gallery--trade-show-graphics.php",
    "template": "raw",
    "content": "pages/gallery--trade-show-graphics.php",
    "service": false,
    "image": "/nyc/assets/mcs-new-service-trade-show-displays-960.webp"
  },
  "/nyc/gallery/vehicle-wraps/": {
    "title": "Vehicle graphics inspiration | Vision Graphics New York",
    "description": "Vehicle examples can help you compare full coverage, partial wraps and focused lettering before choosing a direction.",
    "file": "gallery--vehicle-wraps.php",
    "template": "raw",
    "content": "pages/gallery--vehicle-wraps.php",
    "service": false,
    "image": "/nyc/assets/mcs-new-service-vehicle-wraps-960.webp"
  },
  "/nyc/hanging-signs/": {
    "title": "Hanging signs that use the overhead space | Vision Graphics New York",
    "description": "Hanging signs can make a destination visible when wall space is limited or when a message needs to be seen above surrounding activity.",
    "file": "hanging-signs.php",
    "template": "service",
    "content": "content/services/hanging-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-exterior-signs-960.webp"
  },
  "/nyc/": {
    "title": "Custom Signs in New York | Vision Graphics",
    "description": "Make your next sign work for the way people find, enter and experience your business.",
    "file": "home.php",
    "template": "raw",
    "content": "pages/home.php",
    "service": false,
    "image": "/nyc/assets/mcs-new-home-hero-channel-letters-1600.webp",
    "draft_template": "landing",
    "draft_content": "content/pages/home.json",
    "draft_title": "Custom Signs NYC: Design, Build & Install | Vision Graphics",
    "draft_description": "Custom signs for New York City businesses and property managers. Storefront, lobby, and office signs from design to installation. Send a photo for a quote.",
    "draft_image": "nyc-home-hero-01"
  },
  "/nyc/interior-directory-signs/": {
    "title": "Interior directories that stay organized | Vision Graphics New York",
    "description": "An interior directory helps visitors make sense of a building before they begin searching its corridors.",
    "file": "interior-directory-signs.php",
    "template": "service",
    "content": "content/services/interior-directory-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-building-directory-signs-960.webp"
  },
  "/nyc/interior-signs/": {
    "title": "Interior signs for New York spaces | Vision Graphics New York",
    "description": "Interior signs shape the experience after someone walks through the door.",
    "file": "interior-signs.php",
    "template": "service",
    "content": "content/services/interior-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-interior-signs-960.webp",
    "draft_template": "landing",
    "draft_content": "content/pages/interior-signs.json",
    "draft_title": "Interior Signs NYC: Lobby, Office & ADA | Vision Graphics",
    "draft_description": "Interior signs for New York City offices and buildings. Lobby, office, ADA, and wayfinding signs, designed, made, and installed through one project lead.",
    "draft_image": "nyc-interior-signs-hero-01"
  },
  "/nyc/large-format-printing-services/": {
    "title": "Large format printing planned at full scale | Vision Graphics New York",
    "description": "Large format printing turns artwork into physical graphics that may be viewed across a room, along a storefront or at the scale of a wall.",
    "file": "large-format-printing-services.php",
    "template": "service",
    "content": "content/services/large-format-printing-services.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/lobby-signs/": {
    "title": "Lobby Signs in New York | Vision Graphics",
    "description": "Explore dimensional letters and logo panels for New York reception areas. Learn what affects cost and send your wall photo to start a lobby-sign quote.",
    "file": "lobby-signs.php",
    "template": "service",
    "content": "content/services/lobby-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-hero-image-960.webp"
  },
  "/nyc/magnetic-signs/": {
    "title": "Magnetic signs for suitable removable applications | Vision Graphics New York",
    "description": "A magnetic sign can provide a removable message when the surface and use are appropriate.",
    "file": "magnetic-signs.php",
    "template": "service",
    "content": "content/services/magnetic-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/manhattan/": {
    "title": "Custom Signs & Graphics for Manhattan | Vision Graphics",
    "description": "Plan office signs, window graphics and storefront branding for Manhattan, including Midtown and Chelsea. Review materials, access and installation.",
    "file": "manhattan.php",
    "template": "service",
    "content": "content/services/manhattan.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/metal-signs/": {
    "title": "Metal signs with a considered finish | Vision Graphics New York",
    "description": "Metal can give a sign a crisp edge, a reflective surface or a restrained industrial character.",
    "file": "metal-signs.php",
    "template": "service",
    "content": "content/services/metal-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-metal-signs-960.webp"
  },
  "/nyc/mission-statement-signs/": {
    "title": "Mission and values signs people can read | Vision Graphics New York",
    "description": "A mission statement deserves more thought than placing a paragraph on the largest available wall.",
    "file": "mission-statement-signs.php",
    "template": "service",
    "content": "content/services/mission-statement-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-hero-image-960.webp"
  },
  "/nyc/monument-signs/": {
    "title": "Monument sign concepts for a property entrance | Vision Graphics New York",
    "description": "A monument sign gives a property a fixed point of identification near the ground.",
    "file": "monument-signs.php",
    "template": "service",
    "content": "content/services/monument-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-monument-signs-960.webp"
  },
  "/nyc/office-signs/": {
    "title": "Office Signs in New York | Vision Graphics",
    "description": "Plan coordinated office signs, reception branding and glass graphics in Brooklyn and Manhattan. Build a clear project brief with Vision Graphics.",
    "file": "office-signs.php",
    "template": "service",
    "content": "content/services/office-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-corporate-lobby-signs-business-lobby-signage-960.webp"
  },
  "/nyc/offices-properties/": {
    "title": "Signs for offices and managed properties | Vision Graphics New York",
    "description": "Office and property signage needs to serve both the brand and the building.",
    "file": "offices-properties.php",
    "template": "service",
    "content": "content/services/offices-properties.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-corporate-lobby-signs-business-lobby-signage-960.webp"
  },
  "/nyc/parking-signs/": {
    "title": "Parking and property-use signs | Vision Graphics New York",
    "description": "Parking signs help a property explain where vehicles should go and which spaces have a particular purpose.",
    "file": "parking-signs.php",
    "template": "service",
    "content": "content/services/parking-signs.json",
    "service": true,
    "image": "/nyc/assets/vg-wayfinding-ada-960.webp"
  },
  "/nyc/political-signs/": {
    "title": "Campaign signs with clear approved artwork | Vision Graphics New York",
    "description": "Campaign signs need a concise message, consistent identity and accurate approved content.",
    "file": "political-signs.php",
    "template": "service",
    "content": "content/services/political-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/post-and-panel-signs/": {
    "title": "Post and panel sign concepts for property information | Vision Graphics New York",
    "description": "A post and panel sign places a defined message between or alongside supports, often near a property entrance or an approved temporary display area.",
    "file": "post-and-panel-signs.php",
    "template": "service",
    "content": "content/services/post-and-panel-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/products/": {
    "title": "Explore signs and graphics by purpose | Vision Graphics New York",
    "description": "The most useful place to begin is the job the sign needs to do.",
    "file": "products.php",
    "template": "service",
    "content": "content/services/products.json",
    "service": false,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/projects/big-d-construction/": {
    "title": "Big-D Construction Project | Vision Graphics New York",
    "description": "Vision Graphics supports Big-D Construction, Big-D Signature and Big-D Heavy Industrial with fleet graphics, construction site signage, safety.",
    "file": "projects--big-d-construction.php",
    "template": "raw",
    "content": "pages/projects--big-d-construction.php",
    "service": false,
    "image": "/nyc/assets/vg-project-big-d-construction.webp"
  },
  "/nyc/projects/cafe-rio/": {
    "title": "Café Rio Project | Vision Graphics New York",
    "description": "Vision Graphics worked with Café Rio on repeatable interior graphics, digital displays and branded delivery van graphics. A dedicated ordering portal.",
    "file": "projects--cafe-rio.php",
    "template": "raw",
    "content": "pages/projects--cafe-rio.php",
    "service": false,
    "image": "/nyc/assets/vg-project-cafe-rio.webp"
  },
  "/nyc/projects/eos-fitness/": {
    "title": "EōS Fitness Project | Vision Graphics New York",
    "description": "Vision Graphics supports EōS Fitness with environmental and facility graphics, digital displays, marketing materials and multi-location campaign.",
    "file": "projects--eos-fitness.php",
    "template": "raw",
    "content": "pages/projects--eos-fitness.php",
    "service": false,
    "image": "/nyc/assets/vg-project-eos-fitness.jpg"
  },
  "/nyc/projects/": {
    "title": "VG Project Stories | Vision Graphics New York",
    "description": "Explore genuine Vision Graphics projects for Big-D Construction, Café Rio and EōS Fitness, with ideas for your New York signs and graphics.",
    "file": "projects.php",
    "template": "raw",
    "content": "pages/projects.php",
    "service": false,
    "image": "/nyc/assets/vg-project-big-d-construction.webp"
  },
  "/nyc/pylon-signs/": {
    "title": "Pylon sign concepts for site identification | Vision Graphics New York",
    "description": "A pylon sign is a large site feature whose effectiveness depends on the property layout, approach and surrounding sightlines.",
    "file": "pylon-signs.php",
    "template": "service",
    "content": "content/services/pylon-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-exterior-signs-960.webp"
  },
  "/nyc/real-estate-signs/": {
    "title": "Real estate signs with accurate property information | Vision Graphics New York",
    "description": "Real estate signs connect a property to a clear next action.",
    "file": "real-estate-signs.php",
    "template": "service",
    "content": "content/services/real-estate-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/resources/": {
    "title": "Resources for a better sign project brief | Vision Graphics New York",
    "description": "A little preparation can make a sign project easier to discuss and review.",
    "file": "resources.php",
    "template": "raw",
    "content": "pages/resources.php",
    "service": false,
    "image": "/nyc/assets/vg-logo-dark.webp"
  },
  "/nyc/resources/material-lifecycle/": {
    "title": "Questions to ask about a sign’s useful life | Vision Graphics New York",
    "description": "A thoughtful sign specification considers what happens before and after the first installation.",
    "file": "resources--material-lifecycle.php",
    "template": "service",
    "content": "content/services/resources--material-lifecycle.json",
    "service": false,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/resources/project-brief/": {
    "title": "How to prepare a useful sign project brief | Vision Graphics New York",
    "description": "A sign brief does not need to begin as a technical document.",
    "file": "resources--project-brief.php",
    "template": "service",
    "content": "content/services/resources--project-brief.json",
    "service": false,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/resources/seasonal-signage/": {
    "title": "Plan seasonal graphics as a repeatable program | Vision Graphics New York",
    "description": "Seasonal signage works best when the campaign has a clear message, a defined display period and an agreed plan for removal or reuse.",
    "file": "resources--seasonal-signage.php",
    "template": "service",
    "content": "content/services/resources--seasonal-signage.json",
    "service": false,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/restaurants/": {
    "title": "Signs for restaurants, cafés and bodegas | Vision Graphics New York",
    "description": "A food business has several messages to communicate at once: who you are, what you serve, where to order and what is available now.",
    "file": "restaurants.php",
    "template": "service",
    "content": "content/services/restaurants.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-building-signage-storefronts-960.webp"
  },
  "/nyc/restroom-signs/": {
    "title": "Restroom signs within a clear interior system | Vision Graphics New York",
    "description": "Restroom identification is a small part of a building’s sign program with an important practical role.",
    "file": "restroom-signs.php",
    "template": "service",
    "content": "content/services/restroom-signs.json",
    "service": true,
    "image": "/nyc/assets/vg-wayfinding-ada-960.webp"
  },
  "/nyc/retail/": {
    "title": "Signs for retail and local-service businesses | Vision Graphics New York",
    "description": "A retail or service storefront should make the business recognizable while giving people a reason to step inside.",
    "file": "retail.php",
    "template": "service",
    "content": "content/services/retail.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-building-signage-storefronts-960.webp"
  },
  "/nyc/retractable-banners/": {
    "title": "Retractable banners for a portable introduction | Vision Graphics New York",
    "description": "A retractable banner creates a vertical brand presence in a compact footprint, making it useful for presentations, receptions and business events.",
    "file": "retractable-banners.php",
    "template": "service",
    "content": "content/services/retractable-banners.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-retractable-banners-960.webp"
  },
  "/nyc/school-signs/": {
    "title": "School signs for clear campus communication | Vision Graphics New York",
    "description": "School signage serves students, staff, families and visitors who may be unfamiliar with the building.",
    "file": "school-signs.php",
    "template": "service",
    "content": "content/services/school-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-interior-signs-960.webp"
  },
  "/nyc/sign-installation/": {
    "title": "Sign Installation in NYC: Removal & Repair | Vision Graphics",
    "description": "Sign installation in NYC for offices and storefronts. Old sign removal, wall patching, after-hours installs, and a certificate of insurance for your building.",
    "template": "landing",
    "content": "content/pages/sign-installation.json",
    "service": true,
    "image": "nyc-sign-installation-hero-01",
    "status": "draft"
  },
  "/nyc/signs-for-golf-tournaments/": {
    "title": "Golf tournament signs for an organized event | Vision Graphics New York",
    "description": "Tournament graphics need to welcome participants, recognize sponsors and help people find the right activity without overwhelming the course or venue.",
    "file": "signs-for-golf-tournaments.php",
    "template": "service",
    "content": "content/services/signs-for-golf-tournaments.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-retractable-banners-960.webp"
  },
  "/nyc/storefront-signs/": {
    "title": "Storefront Signs in Brooklyn & Manhattan | Vision Graphics",
    "description": "Plan storefront signs for your New York business. Compare materials, mounting and building requirements, then request a quote from Vision Graphics.",
    "file": "storefront-signs.php",
    "template": "service",
    "content": "content/services/storefront-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-building-signage-storefronts-960.webp"
  },
  "/nyc/trade-show-displays/": {
    "title": "Trade show displays with a clear visitor story | Vision Graphics New York",
    "description": "An event display has a short window to explain who you are and why someone should stop.",
    "file": "trade-show-displays.php",
    "template": "service",
    "content": "content/services/trade-show-displays.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-trade-show-displays-960.webp"
  },
  "/nyc/trailer-wraps/": {
    "title": "Trailer graphics for a large brand canvas | Vision Graphics New York",
    "description": "A trailer can provide generous room for a business identity, but scale alone does not make a message effective.",
    "file": "trailer-wraps.php",
    "template": "service",
    "content": "content/services/trailer-wraps.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-vehicle-wraps-960.webp"
  },
  "/nyc/truck-wraps/": {
    "title": "Truck graphics that respect the body lines | Vision Graphics New York",
    "description": "A truck offers prominent graphic space, but its cab, bed, box and rear doors may each need a different design treatment.",
    "file": "truck-wraps.php",
    "template": "service",
    "content": "content/services/truck-wraps.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-vehicle-wraps-960.webp"
  },
  "/nyc/van-wraps/": {
    "title": "Van wraps and graphics for working teams | Vision Graphics New York",
    "description": "A business van often serves as both transportation and a visible introduction to the team inside.",
    "file": "van-wraps.php",
    "template": "service",
    "content": "content/services/van-wraps.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-vehicle-wraps-960.webp"
  },
  "/nyc/vehicle-graphics/": {
    "title": "Vehicle lettering and focused graphics | Vision Graphics New York",
    "description": "Vehicle graphics do not have to cover every panel to communicate a business identity.",
    "file": "vehicle-graphics.php",
    "template": "service",
    "content": "content/services/vehicle-graphics.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-vehicle-wraps-960.webp"
  },
  "/nyc/vehicle-wraps/": {
    "title": "Vehicle wraps built around the vehicle | Vision Graphics New York",
    "description": "A vehicle wrap turns the body of a car, van or truck into a moving brand surface.",
    "file": "vehicle-wraps.php",
    "template": "service",
    "content": "content/services/vehicle-wraps.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-vehicle-wraps-960.webp"
  },
  "/nyc/vinyl-signs/": {
    "title": "Vinyl graphics for everyday business messages | Vision Graphics New York",
    "description": "Vinyl graphics cover a broad range of jobs: names on a door, promotional artwork on a window, a logo on a vehicle or an identity on an interior wall.",
    "file": "vinyl-signs.php",
    "template": "service",
    "content": "content/services/vinyl-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/wall-decals/": {
    "title": "Wall decals for focused messages | Vision Graphics New York",
    "description": "Sometimes the room needs a few deliberate elements rather than an entire wall treatment.",
    "file": "wall-decals.php",
    "template": "service",
    "content": "content/services/wall-decals.json",
    "service": true,
    "image": "/nyc/assets/mcs-interior-signs-960.webp"
  },
  "/nyc/wall-graphics/": {
    "title": "Custom Wall Graphics for New York Spaces | Vision Graphics",
    "description": "Explore wall graphics, printed wraps and vinyl lettering for New York offices and shops. Review artwork, wall condition, coverage and installation.",
    "file": "wall-graphics.php",
    "template": "service",
    "content": "content/services/wall-graphics.json",
    "service": true,
    "image": "/nyc/assets/mcs-interior-signs-960.webp"
  },
  "/nyc/wall-murals/": {
    "title": "Custom wall murals for immersive interiors | Vision Graphics New York",
    "description": "A wall mural gives imagery the scale of the room.",
    "file": "wall-murals.php",
    "template": "service",
    "content": "content/services/wall-murals.json",
    "service": true,
    "image": "/nyc/assets/mcs-wall-murals-960.webp"
  },
  "/nyc/wayfinding-signs/": {
    "title": "Wayfinding & Directory Signs NYC | Vision Graphics",
    "description": "Plan directories, directional signs and room identification for New York offices and properties. Organize routes, updates and installation scope.",
    "file": "wayfinding-signs.php",
    "template": "service",
    "content": "content/services/wayfinding-signs.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-service-custom-signs-960.webp"
  },
  "/nyc/window-film/": {
    "title": "Office Privacy & Window Film NYC | Vision Graphics",
    "description": "Plan frosted window film, privacy bands and branded glass for New York offices. Review coverage, glass conditions and installation needs for your quote.",
    "file": "window-film.php",
    "template": "service",
    "content": "content/services/window-film.json",
    "service": true,
    "image": "/nyc/assets/mcs-new-corporate-lobby-signs-business-lobby-signage-960.webp"
  },
  "/nyc/window-graphics/": {
    "title": "Window Graphics in Brooklyn & Manhattan | Vision Graphics",
    "description": "Explore window lettering, promotional graphics and privacy treatments for Brooklyn and Manhattan businesses. Send photos and dimensions for a project quote.",
    "file": "window-graphics.php",
    "template": "service",
    "content": "content/services/window-graphics.json",
    "service": true,
    "image": "/nyc/assets/mcs-window-graphics-960.webp"
  }
}
VGNYC_MANIFEST
, true);
