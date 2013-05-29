=== Evaluate ===
Contributors: devindra, enej, ctlt-dev, ubcdev
Tags: comments, rating, pulse, metric, nodejs, dynamic
Requires at least: 3.5
Tested up to: 3.5
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables a widget that allows comments (called pulses) to be posted on any page of the site. Integrates with Stream, Evaluate, and Co Author+ to bring additional functionality to the comment stream.

== Description ==



== Installation ==

1. Extract the zip file into wp-content/plugins/ in your WordPress installation
2. Go to plugins page to activate
3. Thats it! Evaluate supports Multisite too.


== Usage ==

Metrics are what Evaluate uses to keep track of votes for content on your site (like posts, pages, or any public custom post type). These can be questions like "Was this article helpful?", or be untitled and just have an administrative name.

Metrics are usually associated with more than one piece of content. They are displayed in order after the main post content.

= Creating Metrics =

To create a metric nagivate to Metrics -> Add New

Metrics will have a type and style associated with them. These are:
* One way
    * Thumb
    * Arrow (Chevron)
    * Heart
    * Star
* Two way
    * Thumbs
    * Arrows (Chevron)
* Range (Star Rating)
* Poll

Note: extending/customizing the styling options is quite easy (by just changing `img/sprite.gif` or through `css/evaluate.css`).

= Displaying/Hiding Metrics =

By default, Evaluate will display metrics for all content from the type you specified while creating the metric. So, if you create a metric for 'pages', then that metric will be displayed on all content that is a 'page'.

If you want to exclude a particular metric from a specific post or page (or custom post type) you can use the Evaluate meta box when editing a post to choose metrics that you want to exclude from that post.

= How are metrics scored? =

Evaluate scores the content that belong to a metric by user votes.

For one-way metrics (such as a Thumbs Up! for posts), this is just how many votes it has received.

For two-way (up/down vote) metrics, the lower bound of the [Wilson Score interval](http://en.wikipedia.org/wiki/Binomial_proportion_confidence_interval#Wilson_score_interval) is calculated. This tries to better approach the 'real' ratio, of up versus down votes, than other methods, as explained in [How Not to Sort by Average Rating](http://www.evanmiller.org/how-not-to-sort-by-average-rating.html).

Star rating scores are calculated by a simple Bayesian estimate (tending towards 2.5/5). 

Polls are not given a score, but their answers are of course expressed in percentages.

= Sorting Content by Score =

You can add the arguments `?evaluate=sort&sort=<sort_type>&metric_id=<metric_id>` to the url of any page that displays content to sort it by one of it's metric scores. 

<metric_id> should be replaced by the id of the metric you want to sort by. You can see the metric ID on each metric's details page.
<sort_type> can be one of 
 * score, the calculated score for each post. Undefined behaviour for poll metric types.
 * total_votes, the total number of votes that have been made on each post.
 * user_vote, only shows posts that the user has voted on.
Adding `order=<asc|desc>` will determine the order.


== Screenshots ==

1. Metrics displayed underneath the post
2. Customizable styles
3. Metric list


== Changelog ==

= 1.0 =
* Initial release