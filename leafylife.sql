-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 08:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `leafylife`
--

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `content`, `image`, `author`, `created_at`) VALUES
(1, 'Harvesting', 'Harvesting citrus trees is like finding treasure in your own backyard—only instead of gold, you’re rewarded with sweet, tangy fruit! If you\'re reaching for that perfect orange or plucking the juiciest lemon, knowing when and how to harvest makes all the difference. Timing is key, but the real fun begins when you get to taste the fruits of your labor. So, grab your basket and let’s dive into the world of citrus harvesting!\r\n\r\nWhen is the Right Time to Harvest Citrus Fruit?\r\nThe timing of your harvest depends on several factors, including the type of citrus tree, the weather, and the maturity of the fruit. Here are a few general guidelines for knowing when to harvest:\r\n\r\n1. Time of Year\r\n\r\nMost citrus trees have a peak harvest season that corresponds to their variety. Oranges typically harvested from late fall to early spring, while limes are usually harvested from late summer to early fall. Grapefruits ripen in late winter to early spring, and lemons are generally ready to harvest year-round but are best in winter.\r\n\r\n2. Check the Color\r\n\r\nCitrus fruit is usually ripe when it has reached a bright, even color. For example, oranges should be a deep orange, lemons a bright yellow, and limes a full green (though some limes turn yellow when overripe). The color signals that the fruit has developed sugars and is ready for consumption!\r\n\r\n3. Check for Firmness\r\n\r\nRipe citrus should be firm but not hard. A gentle squeeze should leave a slight indentation. If the fruit feels too soft or mushy, it may be overripe or starting to spoil.\r\n\r\n4. Taste Test!\r\n\r\nIf you\'re unsure, the best way to check ripeness is by tasting the fruit. If the fruit is sweet or tart (depending on the variety), it’s a good indicator that it’s ready for harvest. For varieties like limes, which don\'t always change color, the taste test is crucial.\r\n\r\n\r\n\r\nHow to Harvest Citrus Fruit\r\nOnce you\'ve determined that your citrus fruit is ripe, it’s time to harvest. \r\n\r\n1. Use the Right Tools\r\nFor most citrus fruit, you don’t need to use heavy-duty tools, but a pair of clean, sharp pruning shears or garden scissors can help. For larger trees, a harvesting pole with a fruit picker can be useful.\r\n\r\n2. Gently Twist or Cut the Fruit\r\nTo avoid damaging the tree or the fruit, it’s best to twist the fruit gently off the tree or use your pruners to cut the stem. Leave a small part of the stem attached to the fruit to help it last longer. Be cautious not to pull too hard on the fruit, as this can damage the branches.\r\n\r\n3. Handle with Care\r\nCitrus fruit can bruise easily, so handle each piece carefully once it’s harvested. Placing the fruit in a basket or container prevents overcrowding to avoid pressure marks and bruising.\r\n\r\n4. Pick Regularly\r\nIf you have multiple citrus trees, it’s important to harvest regularly, especially if the tree produces a large number of fruit at once. Leaving ripe fruit on the tree for too long can cause it to become overripe and attract pests.\r\n\r\n\r\n\r\nStorage Tips for Citrus Fruit\r\nAfter harvesting, it’s essential to store your fruit properly to maintain freshness and quality. Keep fruit dry to prevent mold, and avoid storing them near bananas, apples, or other ethylene-producing fruits which can accelerate ripening and decay. Most citrus fruit, like oranges and lemons, can be stored at room temperature for up to a week, depending on how ripe they are.\r\n\r\nIf storing in a refrigerator, store in breathable mesh bags or perforated containers and avoid sealed plastic bags, which can trap moisture. Citrus fruits typically last 2-3 weeks in the refrigerator. For long-term storage, citrus fruits can be frozen, either as slices or juice. Simply peel, slice, and freeze on a baking sheet before transferring to a freezer-safe container.\r\n\r\nCommon Harvesting Challenges\r\nIf left too long on the tree, citrus fruit can become overripe, losing its flavor and attracting pests. Keep an eye on your trees, especially as the fruit ripens, to avoid this. They are also prone to pests such as aphids, scale insects, and citrus leaf miners. If you notice pest damage, be sure to address it promptly to protect your harvest.\r\n\r\nConclusion\r\nHarvesting citrus trees is a rewarding process, but it requires timing, care, and attention to detail. By knowing when your citrus fruit is ripe and using proper harvesting methods, you can enjoy a harvest that is rich in flavor and quality!', 'leafylife/images/blog1.webp', 'Jane Green', '2025-03-13 08:25:08'),
(2, 'Growing in Containers', 'Outdoor container gardening is an easy and rewarding way to bring nature closer to home, no matter how small your space is. Growing plants in containers rather than in the ground offers several benefits, including less hassle with weeds, better control over soil quality and pest management, and the flexibility to grow plant varieties that might not typically grow in your climate! With the right plants, containers, and care, you can enjoy homegrown food and beautiful flowers year-round. Below, we’ll cover everything you need to know to create a thriving patio garden—no backyard required.\r\n\r\nAssess Your Space\r\nBefore you start planting, take a look at your patio or balcony and consider how many hours of direct sunlight your space will receive. Most vegetables and flowers need at least 6 hours of direct sunlight daily. You can easily determine which exposure your space faces by using a compass app on your phone. South-facing areas get the most direct sunlight, east-facing receiving morning light, west-facing catching strong afternoon sun, and north-facing being the shadiest. \r\n\r\nBe mindful of obstructions like tree canopies, buildings, or overhangs, as they can block sunlight and create more shade than the patio’s direction alone would suggest. \r\n\r\nPicking the Right Plants \r\nOnce you determine your spaces sunlight exposure, you can choose the best plants that thrive in those conditions and are well-suited for container gardening. Sun-loving plants like citrus and flowering shrubs thrive in full sun, while shade-tolerant options like tropicals and leafy greens do well with less light. Depending on your zone, hardy plants like hydrangeas, evergreen shrubs, and ornamental grasses can stay outdoors year-round in containers, as they can survive winter with proper insulation.\r\n\r\nIf you live in a colder climate, tropical plants that can’t withstand winter temperatures in your region should be brought back inside and treated as a houseplant until conditions warm back up. On the other hand, if you’re planting perennials, woody trees or shrubs in containers outdoors you’ll need to protect the roots from frost come winter. Consider using frost-resistant pots and wrapping containers in burlap or bubble wrap to protect roots from freezing temperatures. Planting in larger containers will also help to insulate the roots, as well as moving planters against a building or in a garage where temperatures are above freezing or just warmer.\r\n\r\nChoosing Containers \r\nThere are a wide variety of container types to choose from depending on your needs including window boxes, hanging baskets, ground planters, and even grow bags. If space is limited, using vertical planters that are wall-mounted or stackable, as well as hanging baskets for trailing plants, are great ways to help maximize space. The most important thing to remember though is to always choose a planter that has drainage to prevent excess water from pooling at the roots, which can cause rot.\r\n\r\nContainers come in various materials, each with unique benefits—terracotta is porous and allows for good airflow but dries out quickly, making it ideal for plants that prefer drier conditions. Plastic and fiberglass planters are lightweight, retain moisture well, and are more resistant to weather changes. Metal, wood, and concrete planters are durable and visually appealing, though metal can overheat in the sun, wood may require sealing, and concrete is heavy but excellent for insulation.\r\n\r\nWhen deciding on container sizes for trees, perennials, or evergreens, try to choose a pot that is at least twice the size of the plant\'s root ball to accommodate future root growth. Trees and large shrubs need deep, sturdy containers (preferably 18-24 inches deep) to support their root systems and to prevent tipping during strong winds. Perennials and evergreens benefit from insulated or thick-walled pots that help regulate soil temperature, especially in colder climates.\r\n\r\nSoil and Fertilizing \r\nGarden soil is too heavy to use in containers and doesn\'t provide adequate pore spaces for aeration and drainage, which can deprive roots of oxygen. It\'ll be ideal to use an organic potting mix that is well-draining and enriched with nutrients for most potted plants. Consider using a species-specific potting mix based on what you\'re growing; for example, succulents thrive in a cactus/succulent potting mix designed for better aeration and drainage to meet their needs.\r\n\r\nPlants in containers need regular fertilizing since nutrients are depleted overtime as your plants grow or can wash out with frequent waterings. You can use a liquid fertilizer throughout the growing season following the application rates which is usually on a bi-weekly to a monthly basis. Slow-release granular fertilizers are also a great option to work into the soil, providing steady nutrients over time to keep plants healthy and thriving.\r\n\r\n\r\nWatering Best Practices\r\nContainerized plants can dry out faster than those planted in the ground, so it would be ideal to check the soil moisture daily, especially during hot, dry periods. Water early in the morning or late in the evening to minimize evaporation and ensure your plants get the moisture they need without the sun causing it to evaporate too quickly. When you water, make sure to water deeply so the soil is evenly and thoroughly saturated until excess water escapes from the drainage holes. This ensures the roots get plenty of moisture as shallow watering can lead to weak root systems. \r\n\r\nYou can easily determine moisture by sticking your finger or a moisture meter into the soil at the depth the plant prefers to dry out. Most plants will prefer water once the top 2-3 inches have dried out, while other drought tolerant species like cacti, prefer to dry out completely. A layer of mulch on top of the soil can help retain moisture and keep the soil temperature stable, which is beneficial in hot weather.\r\nSeasonal Care Tips\r\nSpring-Summer: If you have existing containers outdoors, refresh the soil after the last frost by topping off containers with fresh potting mix. You can also add a slow-release fertilizer to give your plants a head start to the growing season. As the weather warms up water regularly in the early morning or evening to reduce evaporation and use a liquid fertilize throughout the season to keep plants healthy and vibrant! \r\nFall-Winter: For perennials, prune back dead growth to prevent pests and diseases from overwintering. If you plan to leave containers outside, consider moving them to a sheltered area, like near a wall or under an awning, to protect from wind and freezing temperatures. You can also insulate the pots with burlap or foam sleeves and add a layer of mulch over the soil to prevent roots from freezing. Continue to water your plants, but less frequently in winter, especially if the weather is dry. \r\n\r\nConclusion\r\nWhether you\'re working with a small balcony or a spacious patio, container gardening allows you to grow a variety of plants with a little effort and creativity. By choosing the right containers, plants, and care practices, your space can become a lush, inviting oasis. ', 'leafylife/images/blog2.webp', 'John Leaf', '2025-03-13 08:25:08'),
(3, 'How To Keep Your Plants Alive While On Vacation', 'Whether you’ll be spending a long weekend warming up at the beach or a full month at home for the holidays, we’re sharing our top tips and tricks below for keeping your houseplants happy and healthy while you’re away. \r\n\r\nIt only takes a little time to prep your plants so you can focus on more important things, like packing!  \r\n\r\n1. Tweak Light & Temperature\r\nThe more sunlight your plant receives, the more thirsty it will be over time. This is for a few reasons, the biggest being that plants utilize the most water during a process called transpiration, and the rate of transpiration is dependent on, and increases with, the amount of sunlight the plant receives.\r\n\r\nSo the more natural light your plant is getting, the more water it’ll need. To help your plants from wilting while you’re away from lack of water, you can move them a little bit further away from their source of natural light. Place them in the middle of the room so that the heat and light from the windows does not dry them out as fast as usual. Once you return, you can move your plants back to their usual spot. \r\n\r\nIf your plants were not receiving plenty of light to begin with, due to shorter winter days or obstructed windows, you can decide to keep your plants where they are. A good way to determine is to ask yourself how often you have to water a plant—if it\'s every week, you might want to adjust its placement. If it\'s every other week due to lower light levels, no need to move. \r\n\r\nAnd as always—whether you’re home or away—never leave an air conditioning or heating system blasting on or near a houseplant. Although a luxury for humans, ACs and heaters tends to rob your indoor environment of the humidity most tropical plants crave. \r\n\r\n2. Maintain Moisture\r\nIf you plan to be away for a week or less, watering your plants thoroughly before departure will be sufficient. This is especially true during the winter months when plant growth slows and some plants even go semi-dormant. Make sure you are only watering plants with dry or mostly dry potting soil. Let any excess water drain from your potted plant before you’re on your way, so the potting soil is moist but your plants are not sitting in a saucer of water, which could attract pests or lead to root rot. Note this is only necessary for plants that need to be watered once a week or more. Drought-tolerant houseplants, like succulents and cacti, or slow growers due to season, will be fine for a week or two without water. \r\n\r\nWatering Houseplants\r\n\r\nIf you plan to be away for more than a full week, there are a couple of ways to prepare your plant. Try one of the tips below or a combination, depending on the length of your trip, the variety of plant, and the time of year. Keep in mind: how often do I usually water this plant? \r\n\r\n1. Add lava rocks, mulch, or wood chips to the top of your plant’s soil to help hold moisture before giving dry soil a good soaking. Damp newspaper can also do the trick. This will help the soil stay moist for longer. \r\n\r\n2. Water your plant thoroughly and then cover with a clear plastic bag to just below the lip of the planter, creating a makeshift greenhouse. Make sure to cut a couple slits in the plastic to allow for ample air circulation... plants need to breathe, too! Use sticks (or leftover chopsticks) to hold the bag up and away from the foliage. You want to make sure no foliage is touching the bag. \r\n\r\n3. Line a shallow tray with small rocks and fill the tray up with water to slightly beneath the top of the rocks. Set your planter on top of the rocks—the base of the planter should not be touching or sitting directly in the idle water but right above it. This will help to increase humidity and moisture levels, but should not lead to over-watering or root rot.\r\n\r\n4. Transport your humidity-loving plants, like ferns and air plants, to your bathroom (provided you have a window that receives some natural light) or another small room and group them together. The smaller the room, the easier it is for your plants to maintain humidity and moisture. \r\n\r\n5. DIY self-watering system with capillary wicks or empty bottles:\r\n\r\n– Submerge one end of the capillary wick in a basin of water ﻿(choose the size of the water container based on how long you\'ll be away for) and the other end of the wick into your plant\'s potting mix. Your plant will pull the water it needs through the wick while you\'re away. (Our team\'s preferred method!) \r\n\r\n– Upcycle old plastic or glass bottles by filling the bottle with water and puncturing the bottle top. Make sure the hole is small enough that water will be released slowly, over time. Flip your filled bottle upside down and stick the top of the bottle, with the punctured bottle top, deep into your plant’s potting soil.\r\n\r\n6. Call on a friend. If you’re going to be away for an extended period of time (over a month) and have a friend that’s willing to water your houseplants for you—take them up on the offer! Leave your friend with clear written instructions, or walk them through your care routine a week or two beforehand. We won’t judge if you ask them for photo updates while you’re gone. Just make sure to bring them back a souvenir. \r\n\r\n3. Forgo Fertilizer\r\nIf you occasionally use fertilizer on your houseplants, make sure to hold off on fertilizing until you return from your trip. Do not fertilize your plants in the weeks prior to your departure. You’ll want your plants to grow as slowly as possible while you\'re gone, which will help them to conserve energy and water.\r\n\r\n4. Prune Away \r\nIn addition to pruning off any dead, dying, or unhealthy-looking foliage, you can prune off any buds and flowers, which usually require more frequent waterings to stay healthy.\r\n\r\nThe tips above apply to mostly tropical foliage plants. When it comes to drought-tolerant plants like succulents, ZZ plants, and snake plants, they can go over a month without watering, especially if placed out of direct light. If you’re an avid traveler, drought-tolerant plants are the plants for you.\r\n\r\nWhatever preparation you take, give yourself a big pat on the back when you return to a healthy and happy houseplant. It missed you, too. ', 'leafylife/images/blog3.webp', 'Emily Bloom', '2025-03-13 08:25:08'),
(4, 'How to Mulch Your Garden', 'Mulching your garden offers a variety of benefits that contribute to healthier plants and a more sustainable gardening practice. First and foremost, mulch acts as a protective layer that helps retain soil moisture, reducing the need for frequent watering, especially during hot and dry conditions. It also suppresses weed growth by blocking sunlight, which minimizes competition for nutrients and water. Additionally, organic mulches, such as wood chips or straw, break down over time, enriching the soil with essential nutrients and improving its structure.\r\n\r\nMulch can also help regulate soil temperature, keeping it cooler in the summer and warmer in the winter, which promotes more stable growing conditions for your plants. Overall, mulching is an effective and easy way to enhance your garden\'s health and productivity while conserving resources. Below, we explore how to properly mulch and the types of mulches you can use in your garden.\r\n\r\nBefore applying mulch, clear the garden bed or planting area of weeds, debris, and any existing mulch that may be compacted or decomposing. Finally, water the area lightly before applying mulch to help it settle and adhere to the soil.\r\n\r\nTo choose the right mulch for your garden, consider factors such as the plants you\'re growing, the climate, and the purpose of the mulch (e.g., decorative, moisture retention, weed control). Organic mulches like wood chips or straw are great for improving soil quality, while inorganic mulches like gravel or rubber last longer but don’t add nutrients. Lighter mulches work better in wet areas, while heavier ones are good for windy spots. Lastly, match the mulch color and texture to your garden\'s aesthetic for a polished look.\r\n\r\nSpread the mulch evenly over the soil surface. For most plants, a 2-3 inch layer of mulch is ideal. Too little mulch won\'t provide the desired benefits, while too much can suffocate plants and lead to rot. Leave a small gap (about 1-2 inches) around the base of plants, trees, and shrubs. Piling mulch against stems and trunks can cause rot and attract pests.\r\n\r\nOrganic mulches decompose over time and need to be replenished periodically. To replenish, first check the existing layer to see if it\'s broken down or too thin, typically needing about 2-3 inches for proper coverage. Gently rake the old mulch to loosen it, which helps air and water reach the soil. Add a fresh layer of mulch on top, being careful not to pile it against plant stems to avoid rot. Finally, water the area lightly to help the new mulch settle and integrate with the old layer.\r\n\r\nThe best times to add mulch to your garden are in early spring and late fall. In spring, mulch helps retain moisture and control weeds as plants begin to grow, while in fall, it insulates roots and protects them from freezing temperatures. Mulching after planting or during dry periods can also benefit the soil and plants throughout the growing season. If mulching in spring, wait until the soil has warmed up before applying mulch as mulching too early can keep the soil cool and delay plant growth.\r\n\r\nUsing mulch for water conservation helps to reduce evaporation and maintain soil moisture. Organic mulches like straw or wood chips are especially effective as they also improve soil structure over time. Ensure the mulch is evenly distributed and kept away from plant stems to prevent rot while promoting efficient water retention.\r\n\r\nThere are two main categories of mulch: organic and inorganic. Each type has its own set of benefits and uses. Organic mulches are derived from natural materials and decompose over time, adding nutrients to the soil and helps to build good soil structure. While inorganic mulches do not decompose and are often used for decorative purposes or in areas where long-term weed control is needed.\r\n\r\nExamples of Organic Mulches\r\nWood Chips and Bark\r\n\r\nIdeal for garden beds, trees, and shrubs. They decompose slowly, providing long-lasting benefits. Examples include pine bark, cedar chips, and hardwood mulch. Avoid using dyed mulches which can contain harmful chemicals that may leach into the soil and affect plant health.\r\n\r\nThe dyes, particularly in cheaper mulches, may also fade quickly and leave an unnatural look in the garden. They also break down slower, providing fewer nutrients to the soil compared to natural, untreated options.\r\n\r\nStraw\r\n\r\nUsing straw as an organic mulch helps retain moisture in the soil while suppressing weeds, making it an effective option for garden beds. It is lightweight, easy to spread, and breaks down over time, enriching the soil with nutrients as it decomposes. Additionally, straw provides insulation for plant roots during temperature fluctuations, protecting them from extreme heat or cold. Ensure you use weed-free straw to avoid introducing weed seeds.\r\n\r\nGrass Clippings\r\n\r\nGrass clippings helps retain soil moisture and suppress weed growth while providing essential nutrients as they decompose. It\'s best to apply a thin layer, about 1-2 inches, to prevent matting, which can block air and water from reaching the soil. Grass clippings are also a free and readily available resource, making them an eco-friendly choice for garden mulching.\r\n\r\nLeaf Mold\r\n\r\nMade from decomposed leaves, leaf mold is great for improving soil structure and moisture retention. Collect fallen leaves, shred them, and allow them to decompose before use.\r\n\r\nCompost\r\n\r\nNutrient-rich and excellent for vegetable gardens and flower beds. You can use a variety of compost types for your garden beds, including plant-based compost made from vegetable scraps, grass clippings, and leaves, which enrich the soil with nutrients. Manure-based compost from animals like cows, chickens, or horses provides additional nitrogen, promoting plant growth. Worm compost, or vermi-compost, is another option, offering a nutrient-rich, fast-acting fertilizer created by earthworms breaking down organic matter.\r\n\r\nPine Needles\r\n\r\nPine needles offers excellent moisture retention and weed suppression while gradually acidifying the soil, which can benefit acid-loving plants like blueberries and azaleas. They are lightweight and easy to spread, and their long-lasting structure helps prevent erosion. Pine needles also allow water and air to penetrate the soil easily, promoting healthy root development.\r\n\r\nExamples of Inorganic Mulches\r\nGravel and Stone\r\n\r\nIdeal for pathways, driveways, and around plants that prefer dry conditions. Gravel and stone mulches are long-lasting and provide excellent weed control.\r\n\r\nPlastic Mulch\r\n\r\nCommonly used in vegetable gardens to warm the soil, retain moisture, and control weeds. Plastic mulch can be black, red, or clear, each serving different purposes. Ensure adequate irrigation under the plastic.\r\n\r\nRubber Mulch\r\n\r\nMade from recycled tires, rubber mulch is durable and provides good weed control. It is often used in playgrounds and ornamental gardens.\r\n\r\nWhat about using landscape fabric?\r\nLandscape fabric can restrict soil health by limiting the natural exchange of air and nutrients, preventing beneficial microorganisms from thriving. It may also lead to water pooling on top, causing issues with moisture retention and promoting weed growth through gaps or breaks in the fabric. Over time, the fabric can degrade and become entangled with roots, making it difficult to remove and potentially harming the plants.\r\n\r\nConclusion\r\nMulching is a simple yet highly effective way to enhance your garden\'s health and appearance. By selecting the right type of mulch and applying it correctly, you can enjoy a more beautiful, productive, and low-maintenance garden. Whether you choose organic mulches like wood chips and compost or inorganic options like gravel and stone, the benefits of mulching are undeniable.\r\n', 'leafylife/images/blog4.webp', 'Luke Fern', '2025-03-13 08:25:08'),
(5, 'How to Reuse Coffee Grounds to Fertilize Houseplants', 'If you’re a coffee drinker or share your space with one, you’re most likely familiar with coffee grounds. But did you know you can reuse this common kitchen waste item?\r\n\r\nAre Coffee Grounds Good for Plants?\r\nCoffee grounds contain several key nutrients needed by plants, including nitrogen, potassium, magnesium, calcium, and other trace minerals. These are all nutrients that plants need to grow. The grounds are particularly rich in nitrogen, making them a great addition to compost. Additionally, coffee grounds can help to improve the structure and water-retaining abilities of the soil. However, there are some caveats.\r\n\r\nReady to recycle yours? Follow along for our tips and tricks on how to give your coffee grounds a second life.\r\n\r\nHow to Use Coffee Grounds for Plant Care\r\n\r\n1. Opt for used coffee grounds, instead of fresh.\r\nThere are two types of grounds to consider for use with your plants: fresh coffee grounds or used coffee grounds. Fresh grounds are ground-up coffee beans that haven’t been used to brew coffee yet, while used coffee grounds are what’s left over after your coffee has been made.\r\n\r\nWhen considering using coffee grounds to fertilize your houseplants, we recommend sticking with used coffee grounds. This is because fresh grounds can be high in acidity and caffeine, which can have a negative impact on your houseplants. There are a select few plants that can benefit from fresh grounds—including acidity-loving Hydrangeas, Rhododendrons, Gardenias, Azaleas, Lily of the Valley, blueberries, carrots, and radishes—but generally, most common houseplants will prefer low to no acidity.\r\n\r\n2. Add used coffee grounds to your compost.\r\nThere is a lot of conflicting information online on how to repurpose used coffee grounds into fertilizer. The most accepted method, which we also recommend as the best for your plants, is adding the used grounds to your compost.\r\n\r\nTo get started, add the used coffee grounds to your compost pile, which usually consists of vegetable peels, fruit skins, and other types of natural waste. When your compost is ready, mix a small amount of it with potting soil and distribute among your plants.\r\n\r\nDepending on what was in your compost mix, be cautious of how much compost you use. Excessive amounts of compost or coffee grounds can lead to foliage burn and nutrient toxicity. Just like with store-bought fertilizer, a little goes a long way. Compost is filled with rich, organic matter and naturally retains water, so not only will you want to be mindful of how much you mix in your potting soil, but you will also want to be mindful of how often you water your plant potted in it. If you are keen on adding compost to your potting soil for the nutrients, consider also adding coarse sand or perlite to the potting mix to help decrease the risk of overwatering.\r\n\r\n3. Or create a liquid fertilizer with used coffee grounds.\r\nIf you do not compost at home, you can create a liquid fertilizer with your used coffee grounds instead. The key to using used coffee grounds as a liquid fertilizer is dilution! Too much of a good thing is possible, particularly for plants potted in containers. We recommend using about a teaspoon of coffee grounds per gallon of water.\r\n\r\nLet the coffee grounds and water mixture steep for a few nights, stirring occasionally, then strain the liquid through a cheesecloth. The remaining liquid can be used to water, and gently fertilize, your houseplants.\r\n\r\n\r\n4. If you sprinkle used grounds on top of soil, do so sparingly.\r\nAnother way to recycle leftover used coffee grounds is sprinkling them on top of your potting soil. If you plan to test out this method, do so sparingly. Avoid creating a thick layer. Used (and fresh) grounds can lock together and create a barrier to water penetration and air circulation.\r\n\r\n5. Don’t forget other nutrients your plants might need.\r\nReusing used coffee grounds is a fun, free way to fertilizer your houseplants, however, it most likely will not provide your plants with all the nutrients they need. You can add additional nutrients by using a premade houseplant fertilizer or repotting your plant with fresh potting mix.\r\n\r\n6. Recycle used coffee grounds in other ways.\r\nYou may want to test fertilizing plants with and without used coffee grounds. If you aren’t getting the results you hoped for, skip the grounds. Instead, you can recycle used coffee grounds to create a natural cleaning scrub or skin exfoliator.\r\n\r\n\r\nConclusion\r\nUsing used coffee grounds for plants can enrich the soil with nutrients like nitrogen, improve soil structure, and aid in water retention when done in moderation and for plants that thrive in slightly acidic conditions. It’s important to compost the grounds first or mix them with other soil amendments to avoid potential issues like mold growth or excessive acidity. Always monitor your plants\' response to ensure they are thriving with this addition!', 'leafylife/images/blog5.webp', 'Sophia Roots', '2025-03-13 08:25:08');

-- --------------------------------------------------------

--
-- Table structure for table `cancelled_orders`
--

CREATE TABLE `cancelled_orders` (
  `id` varchar(36) NOT NULL,
  `original_order_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `shipping_name` varchar(255) NOT NULL,
  `shipping_mobile` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `cancelled_at` datetime DEFAULT current_timestamp(),
  `agent_name` varchar(255) DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancelled_orders`
--

INSERT INTO `cancelled_orders` (`id`, `original_order_id`, `user_id`, `shipping_name`, `shipping_mobile`, `total_amount`, `shipping_address`, `payment_id`, `status`, `created_at`, `cancelled_at`, `agent_name`, `cancellation_reason`) VALUES
('67f2895e699053.63067485', '1', '2', 'Reena Dubey', '7489580006', 1149.98, 'Reena Dubey, 7489580006, 1087 Sanjeevani Nagar, Jabalpur, Madhya Pradesh, 482003, India', 'pay_QFnVddSfUfkV1g', 'received', '2025-04-06 19:32:00', '2025-04-06 19:32:06', NULL, 'Customer cancelled'),
('67f3ce548dd040.22131209', '4', '4', 'paawni', '6398963065', 399.99, 'paawni, 6398963065, 1087 Sanjeevani Nagar, rishikesh, uk, 244001, India', 'pay_QGB7axKagJN1Fb', 'received', '2025-04-07 18:37:54', '2025-04-07 18:38:36', NULL, 'Customer cancelled'),
('6810c10ea35852.83636677', '5', '1', 'Pranati Dubey', '7828322336', 349.99, 'Pranati Dubey, 7828322336, 1087 Sanjeevani Nagar, Jabalpur, Madhya Pradesh, 482003, India', 'pay_QOrqTnnIFcAUQd', 'received', '2025-04-29 17:37:33', '2025-04-29 17:37:42', NULL, 'Customer cancelled'),
('6810c16655eb25.38079309', '6', '1', 'Pranati Dubey', '7828322336', 599.99, 'Pranati Dubey, 7828322336, 1087 Sanjeevani Nagar, Jabalpur, Madhya Pradesh, 482003, India', 'pay_QOrs3C0q389d2X', 'received', '2025-04-29 17:39:02', '2025-04-29 17:39:10', NULL, 'Customer cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `cancelled_order_items`
--

CREATE TABLE `cancelled_order_items` (
  `id` varchar(36) NOT NULL,
  `cancelled_order_id` varchar(36) DEFAULT NULL,
  `original_order_item_id` varchar(36) NOT NULL,
  `product_id` varchar(36) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancelled_order_items`
--

INSERT INTO `cancelled_order_items` (`id`, `cancelled_order_id`, `original_order_item_id`, `product_id`, `quantity`, `price`) VALUES
('67f2895e6c6a82.17261175', '67f2895e699053.63067485', '1', '9', 1, 549.99),
('67f2895e6cf108.82654762', '67f2895e699053.63067485', '2', '12', 1, 599.99),
('67f3ce5490c608.30127061', '67f3ce548dd040.22131209', '6', '1', 1, 399.99),
('6810c10ea5eca8.96952493', '6810c10ea35852.83636677', '7', '2', 1, 349.99),
('6810c166586f85.92311436', '6810c16655eb25.38079309', '8', '3', 1, 599.99);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`) VALUES
(6, 2, 13, 1);

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `inquiry_type` varchar(50) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_submissions`
--

INSERT INTO `contact_submissions` (`id`, `name`, `email`, `message`, `inquiry_type`, `order_id`, `created_at`, `status`) VALUES
(1, 'Reena Dubey', 'reena.dubey.jabalpur@gmail.com', 'Hi can you give me a quote?', 'general', NULL, '2025-04-29 09:36:24', 'new');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_name` varchar(255) NOT NULL,
  `shipping_mobile` varchar(15) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_id` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'order received',
  `agent_name` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `shipping_name`, `shipping_mobile`, `total_amount`, `shipping_address`, `payment_id`, `created_at`, `status`, `agent_name`) VALUES
(2, 2, 'Reena Dubey', '7489580006', 1798.99, 'Reena Dubey, 7489580006, 1087 Sanjeevani Nagar, Jabalpur, Madhya Pradesh, 482003, India', 'pay_QFqzqQz19ci7BC', '2025-04-06 17:26:40', 'delivered', NULL),
(3, 2, 'Reena Dubey', '7489580006', 499.99, 'Reena Dubey, 7489580006, 1087 Sanjeevani Nagar, Jabalpur, Madhya Pradesh, 482003, India', 'pay_QFr0jFCoZyVy3b', '2025-04-06 17:27:32', 'processing', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(3, 2, 24, 1, 1399.00),
(4, 2, 23, 1, 399.99),
(5, 3, 15, 1, 499.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` float NOT NULL,
  `size` varchar(255) NOT NULL,
  `qty` int(11) UNSIGNED NOT NULL,
  `category` varchar(255) NOT NULL,
  `image1` varchar(255) NOT NULL,
  `image2` varchar(255) NOT NULL,
  `image3` varchar(255) NOT NULL,
  `image4` varchar(255) NOT NULL,
  `LDesc` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `size`, `qty`, `category`, `image1`, `image2`, `image3`, `image4`, `LDesc`) VALUES
(1, 'Bird of Paradise ', 'A striking tropical plant known for its large, banana-like leaves and colorful, bird-shaped flowers. It thrives in bright, indirect light.', 399.99, 'Large', 30, 'New arrivals', 'leafylife\\images\\1_1.webp', 'leafylife\\images\\1_2.webp', 'leafylife\\images\\1_3.webp', 'leafylife\\images\\1_4.webp', 'The Birds of Paradise (Strelitzia reginae) is a bold, tropical plant native to South Africa. It is famous for its large, glossy green leaves and vibrant orange and blue flowers resembling a bird in flight. This plant thrives in bright, indirect sunlight and needs well-drained soil. It can grow up to 5 feet tall indoors, making it an eye-catching addition to any room. Birds of Paradise also benefits from occasional pruning to maintain a neat appearance.'),
(2, 'Large Majesty Palm', 'A general term for large, visually stunning houseplants often characterized by their bold foliage and dramatic height. \r\n', 349.99, 'Large', 25, 'Outdoor', 'leafylife\\images\\2_1.webp', 'leafylife\\images\\2_2.webp', 'leafylife\\images\\2_3.webp', 'leafylife\\images\\2_4.webp', 'The term \"large majestic plant\" often refers to oversized plants that make a statement in any space, such as the Fiddle Leaf Fig or a large Rubber Tree. These plants typically have large, glossy leaves and can grow quite tall, providing an immediate sense of grandeur to a room. They require ample space, bright indirect light, and regular watering. While easy to care for, these plants can be temperamental about their environment, needing consistent humidity and warmth to thrive.'),
(3, 'Snake Plant Laurentii', 'A hardy, low-maintenance plant with tall, upright leaves edged in yellow. It\'s perfect for beginners and can tolerate low light conditions.\r\n', 599.99, 'Small', 23, 'Indoor', 'leafylife\\images\\3_1.webp', 'leafylife\\images\\3_2.webp', 'leafylife\\images\\3_3.webp', 'leafylife\\images\\3_4.webp', 'The Snake Plant (Sansevieria trifasciata Laurentii) is an incredibly tough, drought-tolerant plant known for its upright, sword-like leaves. These leaves are variegated with green and yellow stripes, creating an eye-catching contrast. This plant thrives in low light conditions and requires minimal watering, making it perfect for beginners or those with a busy lifestyle. It also purifies the air by removing toxins, making it a great choice for indoor spaces. Snake plants can grow to a height of 3-4 feet.'),
(4, 'Monstera Deliciosa ', 'A large, tropical vine with distinctive split, glossy leaves. Known for its unique appearance, it\'s often called the \"Swiss cheese plant.\"', 499.99, 'Medium', 20, 'New arrivals', 'leafylife\\images\\4_1.jpg', 'leafylife\\images\\4_2.webp', 'leafylife\\images\\4_3.webp', 'leafylife\\images\\4_4.webp', 'Monstera Deliciosa, also known as the Swiss Cheese Plant, is a popular tropical vine native to the rainforests of Central America. Its large, heart-shaped leaves develop unique holes and splits as they mature, adding a dramatic touch to any room. Monstera enjoys bright, indirect light and regular watering, but it is relatively low-maintenance compared to other tropical plants. It can grow quickly, making it a great choice for creating lush, green spaces. The plant also produces edible fruit, though it\'s rarely seen indoors.'),
(5, 'Olive Tree', 'A Mediterranean tree known for its silvery-green leaves and gnarled trunk, bringing a rustic, timeless touch to interiors.', 599.99, 'Small', 23, 'Pet Friendly', 'leafylife\\images\\5_1.jpg', 'leafylife\\images\\5_2.webp', 'leafylife\\images\\5_3.webp', 'leafylife\\images\\5_4.webp', 'The Olive Tree (Olea europaea) is native to the Mediterranean region and is admired for its silver-green foliage and gnarled, twisted trunk. It can be grown indoors, especially in a pot, but needs plenty of sunlight to thrive. Olive trees require minimal watering once established and prefer a well-draining, slightly alkaline soil. The plant is slow-growing, but with time, it can develop into a stunning small tree. Its fruit, the olive, can be harvested, though it\'s more often seen for ornamental purposes indoors.'),
(6, 'Rubber Tree', 'A large indoor plant with glossy, dark green leaves that can grow into a small tree. It\'s easy to care for and adds a touch of elegance.', 499.99, 'Medium', 30, 'Outdoor', 'leafylife\\images\\6_1.jpg', 'leafylife\\images\\6_2.webp', 'leafylife\\images\\6_3.webp', 'leafylife\\images\\6_4.webp', 'The Rubber Tree (Ficus elastica) is a popular houseplant with glossy, leathery leaves that range from dark green to burgundy, depending on the variety. It thrives in bright, indirect light and requires moderate watering. Rubber trees can grow quite tall, reaching up to 6-8 feet indoors, and make an excellent focal point in living rooms or offices. It’s relatively low-maintenance but benefits from occasional pruning to keep its shape. The plant also helps purify the air, making it an excellent addition to any space.'),
(7, 'African Milk Tree ', 'A striking cactus-like plant with tall, upright stems and a unique, spiny appearance. It can grow up to several feet tall.', 399.99, 'Small', 21, 'Outdoor', 'leafylife\\images\\7_1.jpg', 'leafylife\\images\\7_2.webp', 'leafylife\\images\\7_3.webp', 'leafylife\\images\\7_4.webp', 'The African Milk Tree (Euphorbia trigona) is a dramatic succulent native to West Africa. It features tall, angular stems that are covered in spines and produce a milky sap when cut. The plant thrives in bright, indirect sunlight and needs infrequent watering, making it very low-maintenance. The African Milk Tree is well-suited for dry, warm climates and can grow up to 6 feet tall in ideal conditions. While not a true cactus, its appearance makes it a great addition to succulent and desert-inspired decor.'),
(8, 'Money Plant', 'A hardy plant known for its trailing vines and heart-shaped leaves. It\'s often associated with good luck and fortune.', 499.99, 'Medium', 30, 'Indoor', 'leafylife\\images\\8_1.jpg', 'leafylife\\images\\8_2.webp', 'leafylife\\images\\8_3.webp', 'leafylife\\images\\8_4.webp', 'The Money Plant (Pothos, or Epipremnum aureum) is a popular indoor plant with trailing vines and heart-shaped leaves that can be green or variegated with white, yellow, or light green. It is a resilient plant, thriving in low to medium light and requiring minimal watering. Often used in feng shui, it is believed to bring good luck and prosperity to the home. Money plants are perfect for hanging baskets or as decorative vines on shelves, and they grow quickly with proper care.'),
(9, 'Large Cat Palm', 'An indoor plant with bold, textured leaves that resemble the shape of a cat\'s ears. It\'s a striking addition to any indoor garden.', 549.99, 'Large', 20, 'New arrivals', 'leafylife\\images\\9_1.jpg', 'leafylife\\images\\9_2.webp', 'leafylife\\images\\9_3.webp', 'leafylife\\images\\9_4.webp', 'The Large Cat Plant (Ficus lyrata, commonly known as the Fiddle Leaf Fig) is known for its large, violin-shaped leaves that are often compared to the shape of a cat’s ears. It can grow to a height of up to 6 feet indoors and thrives in bright, indirect light. The plant prefers a warm, humid environment and needs consistent watering. Fiddle Leaf Figs are loved for their dramatic foliage and make a statement in any living room or office space. They do require a bit of attention but reward with lush, vibrant growth.\r\n'),
(10, 'Dracaena Lemon Lime ', 'A compact, colorful plant with bright green and yellow striped leaves, perfect for adding vibrancy to an indoor space.', 699.99, 'Large', 21, 'New arrivals', 'leafylife\\images\\10_1.jpg', 'leafylife\\images\\10_2.webp', 'leafylife\\images\\10_3.webp', 'leafylife\\images\\10_4.webp', 'Dracaena Lemon Lime (Dracaena deremensis) is a popular ornamental plant known for its vibrant green and yellow striped leaves. It thrives in indirect light and does well in moderate indoor temperatures. This plant is relatively low-maintenance, requiring only occasional watering and a well-draining pot. It can grow to a height of about 3-4 feet indoors, making it a great option for adding a pop of color and freshness to any room. Dracaenas are also known for being air-purifying plants.'),
(11, 'Calathea Mediallion', 'A decorative plant known for its round, patterned leaves that feature a rich mix of dark greens and purples.', 399.99, 'Small', 20, 'Pet Friendly', 'leafylife\\images\\11_1.webp', 'leafylife\\images\\11_2.webp', 'leafylife\\images\\11_3.webp', 'leafylife\\images\\11_4.webp', 'The Calathea Medallion is a stunning tropical plant native to South America. Its large, round leaves feature a unique pattern of deep green and purple, making it a striking addition to any home. The plant thrives in low to medium light and requires high humidity to grow well. Regular watering with lukewarm water helps keep the plant happy, as it is sensitive to dry conditions. Calatheas are often called \"prayer plants\" due to their leaves\' movements, which open during the day and close at night.'),
(12, 'Cuddly Cactus', 'A small, soft cactus with a plush-like texture. It\'s easy to care for and adds a quirky touch to any collection.', 599.99, 'Small', 24, 'Outdoor', 'leafylife\\images\\12_1.jpg', 'leafylife\\images\\12_2.webp', 'leafylife\\images\\12_3.webp', 'leafylife\\images\\12_4.jpg', 'The Cuddly Cactus (Mammillaria species) is a small, spherical cactus covered with dense, soft spines, giving it a plush-like appearance. Unlike traditional spiny cacti, its texture is more tactile, making it a fun and safe option for those looking to add a quirky plant to their collection. It thrives in bright light and requires minimal watering. This plant grows slowly and remains compact, making it perfect for small spaces or windowsills. It can also bloom with small, colorful flowers, adding extra appeal.'),
(13, 'Hurricane Fern', 'A fern with unique, curling fronds that resemble the shape of a storm’s swirl. It adds a delicate, airy feel to any room.', 449.99, 'Small', 25, 'Indoor', 'leafylife\\images\\13_1.webp', 'leafylife\\images\\13_2.webp', 'leafylife\\images\\13_3.webp', 'leafylife\\images\\13_4.webp', 'The Hurricane Fern (Nephrolepis exaltata) is a distinctive fern known for its fronds that curl and twist, creating a swirling, hurricane-like pattern. This fern thrives in high humidity and indirect light, making it an excellent choice for bathrooms or kitchens. It requires regular watering but should not be overwatered, as it can rot in soggy soil. With its elegant, flowing fronds, the Hurricane Fern makes a beautiful addition to any indoor space. It’s relatively easy to care for and can grow quite large under the right conditions.'),
(14, 'Bird\'s Nest Fern', 'A fern with wide, glossy, curled fronds that form a central rosette, perfect for shaded indoor environments.', 599.99, 'Small', 25, 'Pet Friendly', 'leafylife\\images\\14_1.webp', 'leafylife\\images\\14_2.webp', 'leafylife\\images\\14_3.webp', 'leafylife\\images\\14_4.webp', 'The Birds Nest Fern (Asplenium nidus) is a unique fern known for its glossy, bright green fronds that form a central rosette, resembling a bird’s nest. It thrives in low to medium light and requires regular watering, though it’s important not to let the soil stay too soggy. Birds Nest Ferns prefer humid environments, so they do well in bathrooms or kitchens. This fern can grow up to 3 feet tall indoors and adds a touch of tropical elegance to any space. It’s also relatively low-maintenance, making it ideal for plant beginners.'),
(15, 'ZZ Plant', 'A resilient, low-light plant with waxy, dark green leaves. It’s virtually indestructible and ideal for beginners.', 499.99, 'Medium', 19, 'Indoor', 'leafylife\\images\\15_1.webp', 'leafylife\\images\\15_2.webp', 'leafylife\\images\\15_3.webp', 'leafylife\\images\\15_4.webp', 'The ZZ Plant (Zamioculcas zamiifolia) is a hardy, low-maintenance plant known for its glossy, dark green leaves. This plant thrives in low to indirect light and requires very little water, making it ideal for beginners or those with a busy lifestyle. The ZZ Plant is also drought-tolerant and can survive periods of neglect, making it one of the toughest indoor plants. It grows slowly, but over time, it can reach up to 3 feet tall. The plant’s waxy leaves help it retain moisture, making it resistant to dry indoor air.'),
(16, 'Stromanthe Triostar', 'A striking plant with variegated leaves in shades of pink, white, and green, creating a tropical look in any room.', 399.99, 'Small', 23, 'New arrivals', 'leafylife\\images\\16_1.webp', 'leafylife\\images\\16_2.webp', 'leafylife\\images\\16_3.webp', 'leafylife\\images\\16_4.webp', 'The Stromanthe Triostar is an ornamental tropical plant known for its stunning, tri-colored leaves. The leaves are a mix of green, pink, and white, making it an eye-catching addition to any space. Stromanthe thrives in indirect light and requires regular watering to keep the soil evenly moist. It also enjoys high humidity, so it’s a great plant for bathrooms or humid environments. This plant can grow up to 2 feet tall indoors and adds a vibrant, tropical feel to any room. Regular misting can help provide the necessary humidity levels. '),
(17, 'Low-Light Duo', 'Enjoy two of our favorite low-light tolerant plants: the Snake plant and the ZZ plant, both of which are very resilient and drought-tolerant. ', 1299, 'Medium', 20, 'Buy 1 Get 1', 'leafylife\\images\\1.1.webp', 'leafylife\\images\\3_3.webp', 'leafylife\\images\\15_2.webp', 'leafylife\\images\\15_3.webp', 'Enjoy two of our favorite low-light tolerant plants: the Snake plant and the ZZ plant, both of which are very resilient and drought-tolerant. These versatile plants are adaptable to a variety of light conditions and are perfect for anyone looking for low-maintenance options (they\'re great for offices, too!).'),
(18, 'The Easy Care Duo', ' The pet-friendly Money Tree is tall and resilient, while the ZZ Plant is drought and low-light tolerant.', 1499, 'Large', 25, 'Buy 1 get 1', 'leafylife\\images\\2.2.webp', 'leafylife\\images\\8_4.webp', 'leafylife\\images\\15_4.webp', 'leafylife\\images\\8_2.webp', 'Save when you buy a plant bundle! This low-maintenance duo is fit for the budding plant parent. The pet-friendly Money Tree is tall and resilient, while the ZZ Plant is drought and low-light tolerant.'),
(19, 'The Palm Bundle ', 'The showy  Majesty Palm and the drought-tolerant Ponytail Palm which isn\'t actually in the palm family, but is called such because of its palm-like fronds.', 1699, 'Large ', 25, 'Buy 1 Get 1', 'leafylife\\images\\3.3.webp', 'leafylife\\images\\2_1.webp', 'leafylife\\images\\9_2.webp', 'leafylife\\images\\2_3.webp', 'The Palm bundle includes our two favorite plants: the showy (and slow-growing, so it won\'t take over) Majesty Palm and the drought-tolerant Ponytail Palm (which isn\'t actually in the palm family, but is called such because of its palm-like fronds). This bundle is great for collectors or those looking to add some tropical-looking greener to their space. Added bonus: Both plants are non-toxic and safe to keep around pets and children.'),
(20, 'Hoya Carnosa Tricolor', 'The Hoya carnosa variegata \'Tricolor\' is an easy-going trailing plant with thick, waxy leaves (which is why they\'re sometimes also called \"wax plants\").', 349, 'Small', 30, 'Summer Collection ', 'leafylife\\images\\17_1.webp', 'leafylife\\images\\17_2.webp', 'leafylife\\images\\17_3.webp', 'leafylife\\images\\17_4.webp', 'The Hoya carnosa variegata \'Tricolor\' is an easy-going trailing plant with thick, waxy leaves (which is why they\'re sometimes also called \"wax plants\"). Vines of playful leaves with green, white and pink variegation will brighten up any room, and the plant may put out dainty, sweet-smelling flowers. Great for hanging baskets or anywhere with space for the vines to trail.'),
(21, 'Philodendron Pink Princess', 'The name Philodendron Pink Princess comes from the leaves that this cultivar shows off, some of which feature bubblegum-pink variegation as they mature.', 399, 'Small', 20, 'Summer Collection ', 'leafylife\\images\\18_1.webp', 'leafylife\\images\\18_2.webp', 'leafylife\\images\\18_3.webp', 'leafylife\\images\\18_4.webp', 'The name Philodendron Pink Princess comes from the leaves that this cultivar shows off, some of which feature bubblegum-pink variegation as they mature. The Pink Princess benefits from bright light, weekly waterings (when the potting mix is dry), higher humidity levels, and a coco coir pole to climb.'),
(22, 'Maranta Lemon Lime', 'Its striking lemon-lime colored leaves adorned with intricate patterns, this plant brings a vibrant burst of color and texture to your living space.', 449, 'Medium', 30, 'Summer Collection', 'leafylife\\images\\19_1.webp', 'leafylife\\images\\19_3.webp', 'leafylife\\images\\19_4.webp', 'leafylife\\images\\19_2.webp', 'The Maranta Lemon Lime, scientifically known as Maranta leuconeura \'Lemon Lime\' is a captivating houseplant cherished for its unique foliage. With its striking lemon-lime colored leaves adorned with intricate patterns, this plant brings a vibrant burst of color and texture to your living space.'),
(23, 'Peperomia Raindrop', 'The Peperomia Raindrop, also called the coin plant, is sometimes mistaken for the Pilea Peperomioides due to its compact growth habit and thick green leaves.', 399.99, 'Small', 24, 'Summer Collection', 'leafylife\\images\\20_1.webp', 'leafylife\\images\\20_2.webp', 'leafylife\\images\\20_3.webp', 'leafylife\\images\\20_4.webp', 'The Peperomia Raindrop, also called the Peperomia Polybotrya, coin plant, or coin-leaf peperomia, is sometimes mistaken for the Pilea Peperomioides due to its compact growth habit and thick green leaves. However, if you look closely, you’ll notice the leaves are more teardrop-shaped. Like other peperomias, the Raindrop is a hardy, pet-friendly houseplant, safe to keep around curious cats and dogs.'),
(24, 'Modern Office Duo', 'Enjoy two of our favorite versatile plants: the Snake plant and the Dracaena compacta, both of which are resilient and drought-tolerant.', 1399, 'Large', 19, 'Buy 1 Get 1', 'leafylife\\images\\22_1.jpeg', 'leafylife\\images\\3_1.webp', 'leafylife\\images\\10_4.webp', 'leafylife\\images\\10_1.jpg', 'Enjoy two of our favorite versatile plants: the Snake plant and the Dracaena compacta, both of which are resilient and drought-tolerant. These easy-to-care for plants are adaptable to a variety of light conditions and are perfect for anyone looking for low-maintenance options for their home or office!');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(4, 23, 2, 5, 'Great product with prompt delivery!', '2025-04-06 17:28:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `isadmin` tinyint(1) DEFAULT 0,
  `address_street` varchar(255) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_state` varchar(100) DEFAULT NULL,
  `address_zip` varchar(10) DEFAULT NULL,
  `address_country` varchar(10) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `mobile`, `email`, `password`, `isadmin`, `address_street`, `address_city`, `address_state`, `address_zip`, `address_country`, `security_question`, `security_answer`) VALUES
(1, 'Pranati Dubey', '7828322336', 'pranati.dubey2004@gmail.com', '$2y$10$qhTArJNg8b1P1VrCMIvaZO8keZxwmF2eKm5Ncfbp4wZO5BPfRQnQC', 1, '1087 Sanjeevani Nagar', 'Jabalpur', 'Madhya Pradesh', '482003', 'India', 'pet', 'brown'),
(2, 'Reena Dubey', '7489580006', 'reena.dubey.jabalpur@gmail.com', '$2y$10$glyKe0EJO8s0NL86k6PMBuso3RsC4747IAATlSDZoVZct/P39QPYq', 0, '1087 Sanjeevani Nagar', 'Jabalpur', 'Madhya Pradesh', '482003', 'India', 'pet', 'mithu'),
(3, 'Vaani Misra', '9837027771', 'varumisra22@gmail.com', '$2y$10$rglLHiZQ9uD8wRq6QEhu9.vlPHSSjaZ.gukN3GeBR/td7WrpqNH1u', 1, 'B19 naveen nagar', 'moradabad', 'Uttar Pradesh', '244001', 'India', 'city', 'mbd'),
(4, 'paawni', '6398963065', 'Paawni@gmail.com', '$2y$10$bxKmAw5upZyLSlU9DWhpNOlfcTCP256Av3Gn.ni28J44c.MInPXWu', 0, '1087 Sanjeevani Nagar', 'rishikesh', 'uk', '244001', 'India', 'pet', 'junior');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `user_id`, `product_id`) VALUES
(1, 2, 16),
(3, 4, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cancelled_orders`
--
ALTER TABLE `cancelled_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cancelled_order_items`
--
ALTER TABLE `cancelled_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cancelled_order_id` (`cancelled_order_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cancelled_order_items`
--
ALTER TABLE `cancelled_order_items`
  ADD CONSTRAINT `cancelled_order_items_ibfk_1` FOREIGN KEY (`cancelled_order_id`) REFERENCES `cancelled_orders` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD CONSTRAINT `contact_submissions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
