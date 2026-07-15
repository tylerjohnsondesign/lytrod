//FRAMEWORK SETTINGS: THE EDITOR CONFIGURATION ///////////////

//why using objects?
//https://www.stefanjudis.com/today-i-learned/property-order-is-predictable-in-javascript-objects-since-es2015/

//general policy
//put all bootstrap property info, add " when needed in array values, then
//add to property object: about - docs - class (if not specified)
//
//class is the prefix for all classes, that is added to each elemeent before array

//DEFINE FRAMEWORK SETTINGS OBJECT
let theFramework = {
	name: "DaisyUI",
	version: "5",
	uses: "Tailwind 4",
	documentation: "#",
	breakpoints: {
		"SM": {
			name: "Small",
			infix: "sm",
			dimensions: "<640px"
		},
		"MD": {
			name: "Medium",
			infix: "md",
			dimensions: "≥768px"
		},
		"LG": {
			name: "Large",
			infix: "lg",
			dimensions: "≥1024px"
		},
		"XL": {
			name: "Extra large",
			infix: "xl",
			dimensions: "≥1280px"
		},
		"2XL": {
			name: "Extra extra large",
			infix: "2xl",
			dimensions: "≥1536px"
		}
	},
	properties: {
		"Colors": {
			"Background": {
				"color": {
					property: "background",
					class: "bg",
					widget: "colors",
					values: ["primary", "secondary", "success", "danger", "warning", "info", "light", "dark", "body", "white", "transparent"],
					shades: {
						values: ["primary", "secondary", "success", "danger", "warning", "info", "light", "dark",],
						strengths: [25, 50, 100, 200, 300, 400, 500, 600, 700, 800, 900],
					},
					about: "Change background color",
					docs: "https://getbootstrap.com/docs/5.3/utilities/background/#background-color",
				},
				"gradient": {
					property: "background",
					widget: "select",
					values: ["bg-gradient"],
					about: "Adds a linear gradient",
					docs: "https://getbootstrap.com/docs/5.3/utilities/background/#background-gradient",
					class: ""
				},
				"opacity": {
					property: "background",
					widget: "select",
					values: ["10", "25", "50", "75",],
					about: "Adds a linear gradient via  background image",
					docs: "https://getbootstrap.com/docs/5.3/utilities/background/#opacity",
					class: "bg-opacity"
				},
			},
			"Text": {
				"color": {
					property: "color",
					class: "text",
					widget: "colors",
					values: ["primary", "secondary", "success", "danger", "warning", "info", "light", "dark", "body", "muted", "white", /* "black-50", "white-50", */ "reset"],
					shades: {
						values: ["primary", "secondary", "success", "danger", "warning", "info", "light", "dark",],
						strengths: [25, 50, 100, 200, 300, 400, 500, 600, 700, 800, 900],
					},
					about: "Colorize text",
					docs: "https://getbootstrap.com/docs/5.3/utilities/colors/",
				},
				"opacity": {
					property: "opacity",
					widget: "select",
					values: ["75", "50", "25"],
					about: "Set text color opacity",
					docs: "https://getbootstrap.com/docs/5.3/utilities/colors/#opacity",
					class: "text-opacity"
				},
			},

		}, 	"Layout": {
			"General": {
				"display": {
					responsive: true,
					print: true,
					property: "display",
					class: "d",
					widget: "select",
					values: ["inline", "inline-block", "block", "grid", "inline-grid", "table", "table-row", "table-cell", "flex", "inline-flex", "none"],
					about: "Quickly and responsively toggle the display value of components and more. ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/display/",
				},
				"float": {
					responsive: true,
					property: "float",
					widget: "icons",
					values: ["start", "end", "none"],
					about: "These utility classes float an element to the left or right, or disable floating, based on the current viewport size using the CSS float property. ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/float/",
					class: "float",
				},
			},
			"Text Alignment": {
				"Text Alignment": {
					responsive: true,
					print: true,
					property: "display",
					class: "text",
					widget: "icons",
					values: ["start", "center", "end"],
					about: "Easily realign text to components with text alignment classes. ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/text/#text-alignment",
				},
				"Vertical Alignment": {
					property: "vertical-align",
					widget: "select",
					values: ["baseline", "top", "middle", "bottom", "text-bottom", "text-top"],
					about: "Easily change the vertical alignment of inline, inline-block, inline-table, and table cell elements.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/vertical-align/",
					class: "align",
				},
			},
			"Children Spacing": {
				"gutters": {
					responsive: true,
					property: "gutters",
					class: "g",
					widget: "numeric",
					min: 0,
					max: 5,
					about: "Gutters are the padding between your columns, used to responsively space and align content in the Bootstrap grid system.",
					docs: "https://getbootstrap.com/docs/5.3/layout/gutters/#horizontal--vertical-gutters",
				},
				"gap": {
					responsive: true,
					property: "gap",
					widget: "numeric",
					min: 0,
					max: 10,
					about: "These utility classes float an element to the left or right, or disable floating, based on the current viewport size using the CSS float property. ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/#gap",
					class: "gap",

				},
			},
			"Flex": {
				"flex-direction": {
					responsive: true,
					property: "flex-direction",
					class: "flex",
					widget: "icons",
					values: ["row", "column", "row-reverse", "column-reverse"],
					about: "Set the direction of flex items in a flex container with direction utilities. In most cases you can omit the horizontal class here as the browser default is row.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#direction",
				},
				"flex-wrap": {
					responsive: true,
					property: "flex-wrap",
					class: "flex",
					widget: "icons",
					values: ["wrap", "nowrap", "wrap-reverse"],
					about: "Change how flex items wrap in a flex container. ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#wrap",
				},
				"justify-content": {
					responsive: true,
					property: "justify-content",
					class: "justify-content",
					widget: "icons",
					values: ["start", "end", "center", "between", "around", "evenly",],
					about: "Use justify-content utilities on flexbox containers to change the alignment of flex items on the main axis",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#justify-content",
				},
				"align-items": {
					responsive: true,
					property: "align-items",
					class: "align-items",
					widget: "icons",
					values: ["start", "end", "center", "baseline", "stretch",],
					about: "Use align-items utilities on flexbox containers to change the alignment of flex items on the cross axis (the y-axis to start, x-axis if flex-direction: column). Choose from start, end, center, baseline, or stretch (browser default).",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#align-items",
				},
				"align-content": {
					responsive: true,
					property: "align-content",
					class: "align-content",
					widget: "icons",
					values: ["start", "end", "center", "between", "around", "stretch",],
					about: "Use align-content utilities on flexbox containers to align flex items together on the cross axis.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#align-content",
				},
			},
			"Flex Child": {
				"order": {
					responsive: true,
					property: "flex-order",
					class: "order",
					widget: "select",
					values: ["first", "0", "1", "2", "3", "4", "5", "last"],
					about: "Change the visual order of specific flex items with a handful of order utilities.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#order",
				},
				"Flex Grow": {
					responsive: true,
					property: "flex-grow",
					class: "flex",
					widget: "select",
					values: ["grow-0", "grow-1"],
					about: "Use .flex-grow-* utilities to toggle a flex item’s ability to grow to fill available space. ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#grow-and-shrink",
				},
				"Flex Shrink": {
					responsive: true,
					property: "flex-shrink",
					class: "flex",
					widget: "select",
					values: ["shrink-0", "shrink-1"],
					about: "Use .flex-shrink-* utilities to toggle a flex item’s ability to shrink if necessary. ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#grow-and-shrink",
				},
				"Flex Fill": {
					responsive: true,
					property: "flex-fill",
					class: "flex",
					widget: "select",
					values: ["fill"],
					about: "",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#fill",
				},
				"Align Self": {
					responsive: true,
					property: "align-self",
					class: "align-self",
					widget: "icons",
					values: ["auto", "start", "end", "center", "baseline", "stretch"],
					about: "Use align-self utilities on flexbox items to individually change their alignment on the cross axis",
					docs: "https://getbootstrap.com/docs/5.3/utilities/flex/#align-self",
				}
			},

		},
		"Spacing": {
			"Margin": {
				"margin-align-start": {
					responsive: true,
					property: "margin",
					class: "ms",
					widget: "icons",
					values: ["auto",],
					about: "",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/#margin-and-padding",
				},
				"margin-align-center": {
					responsive: true,
					property: "margin",
					class: "mx",
					widget: "icons",
					values: ["auto",],
					about: "Additionally, Bootstrap also includes an .mx-auto class for horizontally centering fixed-width block level content—that is, content that has display: block and a width set—by setting the horizontal margins to auto.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/#horizontal-centering",
				},
				"margin-align-end": {
					responsive: true,
					property: "margin",
					class: "me",
					widget: "icons",
					values: ["auto",],
					about: "",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/#margin-and-padding",
				},

				"margin-top": {
					responsive: true,
					property: "margin-top",
					class: "mt",
					min: -10,
					max: 10,
					widget: "numeric",
					about: "Bootstrap includes a wide range of shorthand responsive margin, padding, and gap utility classes to modify an element’s appearance.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/"
				},

				"margin-right": {
					responsive: true,
					property: "margin-right",
					class: "me",
					min: -10,
					max: 10,
					widget: "numeric",
					about: "Bootstrap includes a wide range of shorthand responsive margin, padding, and gap utility classes to modify an elements appearance.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/"
				},
				"margin-bottom": {
					responsive: true,
					property: "margin-bottom",
					class: "mb",
					min: -10,
					max: 10,
					widget: "numeric",
					about: "Bootstrap includes a wide range of shorthand responsive margin, padding, and gap utility classes to modify an elements appearance.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/"
				},
				"margin-left": {
					responsive: true,
					property: "margin-left",
					class: "ms",
					min: -10,
					max: 10,
					widget: "numeric",
					about: "Bootstrap includes a wide range of shorthand responsive margin, padding, and gap utility classes to modify an elements appearance.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/"
				},
			},

			"Padding": {
				"padding-top": {
					responsive: true,
					property: "padding-top",
					class: "pt",
					min: 0,
					max: 10,
					widget: "numeric",
					about: "Bootstrap includes a wide range of shorthand responsive margin, padding, and gap utility classes to modify an element’s appearance.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/"
				},

				"padding-right": {
					responsive: true,
					property: "padding-right",
					class: "pe",
					min: 0,
					max: 10,
					widget: "numeric",
					about: "Bootstrap includes a wide range of shorthand responsive margin, padding, and gap utility classes to modify an elements appearance.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/"
				},
				"padding-bottom": {
					responsive: true,
					property: "padding-bottom",
					class: "pb",
					min: 0,
					max: 10,
					widget: "numeric",
					about: "Bootstrap includes a wide range of shorthand responsive margin, padding, and gap utility classes to modify an elements appearance.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/"
				},
				"padding-left": {
					responsive: true,
					property: "padding-left",
					class: "ps",
					min: 0,
					max: 10,
					widget: "numeric",
					about: "Bootstrap includes a wide range of shorthand responsive margin, padding, and gap utility classes to modify an elements appearance.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/spacing/"
				},
			},



		},
		"Sizing": {
			"Widths": {
				"width": {
					property: "width",
					class: "w",
					widget: "select",
					values: ["5", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55", "60", "65", "70", "75", "80", "85", "90", "95", "100", "auto"],
					about: " ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/sizing/",
				},
				"max-width": {
					responsive: true,
					property: "max-width",
					class: "mw",
					widget: "select",
					values: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "none"],
					about: " ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/sizing/",
				},
				"viewport-width": {
					property: "width",
					class: "vw",
					widget: "select",
					values: ["100"],
					about: " ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/sizing/",
				},
				"min-viewport-width": {
					property: "width",
					class: "min-vw",
					widget: "select",
					values: ["25", "50", "75", "100"],
					about: " ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/sizing/",
				},

			},
			"Heights": {
				"height": {
					property: "height",
					class: "h",
					widget: "select",
					values: ["5", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55", "60", "65", "70", "75", "80", "85", "90", "95", "100", "auto"],
					about: " ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/sizing/",
				},
				"max-height": {
					property: "max-height",
					class: "mh",
					widget: "select",
					values: ["100"],
					about: " ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/sizing/",
				},
				"viewport-height": {
					property: "height",
					class: "vh",
					widget: "select",
					values: ["100"],
					about: " ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/sizing/",
				},
				"min-viewport-height": {
					responsive: true,
					property: "height",
					class: "min-vh",
					widget: "select",
					values: ["25", "50", "75", "100"],
					about: " ",
					docs: "https://getbootstrap.com/docs/5.3/utilities/sizing/",
				},

			},
			"Utility": {
				"overflow": {
					property: "overflow",
					class: "overflow",
					widget: "select",
					values: ["auto", "hidden", "visible", "scroll"],
					about: "Use these shorthand utilities for quickly configuring how content overflows an element.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/overflow/",
				},


			},
			"Column": {
				"Column Size": {
					responsive: true,
					property: "width",
					class: "col",
					widget: "numeric",
					min: 0,
					max: 12,
					about: "Use our powerful mobile-first flexbox grid to build layouts of all shapes and sizes thanks to a twelve column system, six default responsive tiers, Sass variables and mixins, and dozens of predefined classes.",
					docs: "https://getbootstrap.com/docs/5.3/layout/columns/",
				},
				"Column Offset": {
					responsive: true,
					property: "",
					class: "offset",
					widget: "numeric",
					min: 0,
					max: 12,
					about: "Move columns to the right",
					docs: "https://getbootstrap.com/docs/5.3/layout/columns/#offsetting-columns",
				},

			},
			"Container": {
				"Container Width": {
					property: "width",
					widget: "select",
					values: ["", "sm", "md", "lg", "xl", "xxl", "fluid"],
					about: "Containers are a fundamental building block of Bootstrap that contain, pad, and align your content within a given device or viewport.",
					docs: "https://getbootstrap.com/docs/5.3/layout/containers/",
					class: "container"
				},
			}

		},
		"Position": {
			"General": {
				"Position": {
					responsive: true,
					property: "position",
					widget: "select",
					values: ["static", "relative", "absolute", "fixed", "sticky"],
					about: "Use these shorthand utilities for quickly configuring the position of an element.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/position/",
					class: "position"
				},
				"Top": {
					responsive: true,
					property: "top",
					widget: "select",
					values: ["5", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55", "60", "65", "70", "75", "80", "85", "90", "95", "100", "auto"],
					about: "Vertical top position",
					docs: "https://getbootstrap.com/docs/5.3/utilities/position/#arrange-elements",
					class: "top"
				},
				"Bottom": {
					responsive: true,
					property: "bottom",
					widget: "select",
					values: ["5", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55", "60", "65", "70", "75", "80", "85", "90", "95", "100", "auto"],
					about: "Vertical bottom position",
					docs: "https://getbootstrap.com/docs/5.3/utilities/position/#arrange-elements",
					class: "bottom"
				},
				"Start": {
					responsive: true,
					property: "left",
					widget: "select",
					values: ["5", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55", "60", "65", "70", "75", "80", "85", "90", "95", "100", "auto"],
					about: "Horizontal left position (in LTR)",
					docs: "https://getbootstrap.com/docs/5.3/utilities/position/#arrange-elements",
					class: "start"
				},
				"End": {
					responsive: true,
					property: "right",
					widget: "select",
					values: ["5", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55", "60", "65", "70", "75", "80", "85", "90", "95", "100", "auto"],
					about: "Horizontal right position (in LTR)",
					docs: "https://getbootstrap.com/docs/5.3/utilities/position/#arrange-elements",
					class: "end"
				},
				"Translate Middle": {
					responsive: true,
					property: "transform",
					widget: "select",
					values: ["", "x", "y"],
					about: "Center the elements with the transform utility class",
					docs: "https://getbootstrap.com/docs/5.3/utilities/position/#center-elements",
					class: "translate-middle"
				},



			},
			"Helpers": {
				"Fixed": {
					property: "position",
					widget: "select",
					values: ["top", "bottom"],
					about: "Use these helpers for quickly configuring the position of an element.",
					docs: "https://getbootstrap.com/docs/5.3/helpers/position/#fixed-top",
					class: "fixed"
				},
				"Sticky": {
					responsive: true,
					property: "position",
					widget: "select",
					values: ["top", "bottom"],
					about: "Position an element at the top of the viewport, from edge to edge, but only after you scroll past it.",
					docs: "https://getbootstrap.com/docs/5.3/helpers/position/#sticky-top",
					class: "sticky",
				},


				"Visibility": {
					property: "visibility",
					widget: "select",
					values: ["visible", "invisible"],
					about: "Control the visibility of elements, without modifying their display, with visibility utilities.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/visibility/",
					class: "",
				},
				"Clearfix": {
					property: "clear",
					widget: "select",
					values: ["clearfix"],
					about: "Quickly and easily clear floated content within a container by adding a clearfix utility.",
					docs: "https://getbootstrap.com/docs/5.3/helpers/clearfix/",
					class: "",
				},

			},

		},
		"Borders": {
			"General": {
				"additive-border": {
					class: "border",
					widget: "select",
					values: ["", "top", "bottom", "start", "end",],
					about: "Use border utilities to quickly style the border and border-radius of an element. Great for images, buttons, or any other element.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/borders/#additive",
				},
				"Color": {
					property: "border-color",
					class: "border",
					widget: "colors",
					values: ["primary", "secondary", "success", "danger", "warning", "info", "light", "dark", "body", "muted", "white"],
					about: "Colorize borders",
					docs: "https://getbootstrap.com/docs/5.3/utilities/borders/#color",
				},
				"Opacity": {
					property: "opacity",
					widget: "select",
					values: ["75", "50", "25"],
					about: "Set border color opacity",
					docs: "https://getbootstrap.com/docs/5.3/utilities/borders/#opacity",
					class: "border-opacity"
				},
				"Width": {
					property: "",
					widget: "select",
					values: ["1", "2", "3", "4", "5"],
					about: "Set border color width",
					docs: "https://getbootstrap.com/docs/5.3/utilities/borders/#width",
					class: "border"
				},
				"Radius": {
					property: "",
					widget: "select",
					values: ["", "top", "end", "bottom", "start", "circle"],
					about: "Set border color width",
					docs: "https://getbootstrap.com/docs/5.3/utilities/borders/#radius",
					class: "rounded"
				},
				"Sizes": {
					property: "",
					widget: "select",
					values: ["1", "2", "3", "4", "5"],
					about: "Use the scaling classes for larger or smaller rounded corners.",
					docs: "https://getbootstrap.com/docs/5.3//utilities/borders/#sizes",
					class: "rounded"
				},
			},

			/* "Responsive border": {
				"responsive-border-start": {
					class: "border-start",
					widget: "select",
					values: ["0","sm","md","lg","xl","xxl"],
					about: "The border will be displayed only from the selected breakpoint onwards.",
					docs: "https://bootstrap.ninja/ninjabootstrap/#borders",
				},
				"responsive-border-top": {
					class: "border-top",
					widget: "select",
					values: ["0","sm","md","lg","xl","xxl"],
					about: "The border will be displayed only from the selected breakpoint onwards.",
					docs: "https://bootstrap.ninja/ninjabootstrap/#borders",
				},
				"responsive-border-bottom": {
					class: "border-bottom",
					widget: "select",
					values: ["0","sm","md","lg","xl","xxl"],
					about: "The border will be displayed only from the selected breakpoint onwards.",
					docs: "https://bootstrap.ninja/ninjabootstrap/#borders",
				},
				"responsive-border-end": {
					class: "border-end",
					widget: "select",
					values: ["0","sm","md","lg","xl","xxl"],
					about: "The border will be displayed only from the selected breakpoint onwards.",
					docs: "https://bootstrap.ninja/ninjabootstrap/#borders",
				},
			}, */
		},
		"Decoration": {
			"General": {
				"Background image": {
					widget: "custom",
					about: "Adds a custom background image to the element.",
					custom: ` <div> <label>Background <a style="float:right;text-decoration: underline" class="open-background-image-panel" href="#">Image</a></label> <ul class="ul-to-selection" id="backgrounds"> <li class="first" style="background:rgba(0, 0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box"></li> <li style=""></li> <div class="automatic-library-filler" the-style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/@id@.svg)  50% 50% / cover no-repeat;" max="18"><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/1.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/2.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/3.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/4.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/5.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/6.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/7.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/8.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/9.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/10.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/11.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/12.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/13.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/14.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/15.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/16.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/17.svg)  50% 50% / cover no-repeat;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/trianglify/18.svg)  50% 50% / cover no-repeat;"></li></div> <div class="automatic-library-filler" the-style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/@id@.png)  50% 50%;" max="14"><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/1.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/2.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/3.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/4.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/5.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/6.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/7.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/8.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/9.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/10.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/11.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/12.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/13.png)  50% 50%;"></li><li style="background:url(https://cdn.livecanvas.com/media/backgrounds/pattern/14.png)  50% 50%;"></li></div> </ul> </div> `,
				},
				"Shape Divider": {
					widget: "custom",
					about: " Adds an SVG item at the bottom of the element. You may have to assign a backgound to see the effect.",
					custom: `<ul class="ul-to-selection" id="shape_dividers"> <li class="first"></li> <li></li> <li> <svg class="lc-shape-divider-bottom" viewBox="0 0 140 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"> <path d="M0 24H1440V0C722.5 52 0 0 0 0V24Z" fill="white"></path> </svg> </li> <li> <svg class="lc-shape-divider-bottom" style=" fill:white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 206.86" preserveAspectRatio="none"> <path d="M475.75 65c85.1-33.38 176.3-53 268-48.16a485.87 485.87 0 0 1 122.69 22.3A620.49 620.49 0 0 0 769 11.3c-166-32.36-329.9 9.06-482 69.91-98.73 39.51-191.5 86.25-287 125.65h167c65.37-30.67 129.71-65 197.67-94.61C400.93 96.47 438 79.79 475.75 65z" opacity=".15"></path> <path d="M741.62 52.76c-129.82-27.54-258 7.7-376.92 59.49-68 29.59-132.3 63.94-197.67 94.61h833v-9.09C930.63 126.88 832.81 72.1 741.62 52.76z"></path> <path d="M866.44 39.14a485.87 485.87 0 0 0-122.66-22.31C652.05 12 560.85 31.61 475.75 65c-37.73 14.8-74.82 31.48-111 47.26 118.93-51.79 247.1-87 376.92-59.49 91.19 19.34 189 74.12 258.38 145v-84.5a329.47 329.47 0 0 0-50-36.65 723 723 0 0 0-83.61-37.48z" opacity=".3"></path></svg> </li> <li> <svg class="lc-shape-divider-bottom" style=" fill:white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4200 512" preserveAspectRatio="xMidYMin slice"> <path style="isolation:isolate" d="M200 500l100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100V400l-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100L0 400v100l100-100z" opacity=".75"></path> <path d="M4200 500l-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100L0 500v12h4200z"></path> <path style="isolation:isolate" d="M200 400l100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100V300l-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100L0 300v100l100-100z" opacity=".5"></path> <path style="isolation:isolate" d="M200 300l100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100V200l-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100-100 100-100-100L0 200v100l100-100z" opacity=".25"></path> <path style="isolation:isolate" d="M200 200l100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100 100-100 100 100V100L4100 0l-100 100L3900 0l-100 100L3700 0l-100 100L3500 0l-100 100L3300 0l-100 100L3100 0l-100 100L2900 0l-100 100L2700 0l-100 100L2500 0l-100 100L2300 0l-100 100L2100 0l-100 100L1900 0l-100 100L1700 0l-100 100L1500 0l-100 100L1300 0l-100 100L1100 0l-100 100L900 0 800 100 700 0 600 100 500 0 400 100 300 0 200 100 100 0 0 100v100l100-100z" opacity=".12"></path></svg> </li> <li> <svg class="lc-shape-divider-bottom" style=" fill:white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 279.24" preserveAspectRatio="none"> <path d="M1000 0S331.54-4.18 0 279.24h1000z" opacity=".25"></path> <path d="M1000 279.24s-339.56-44.3-522.95-109.6S132.86 23.76 0 25.15v254.09z"></path></svg> </li> <li> <svg class="lc-shape-divider-bottom" width="100%" style="height: 6vh;" version="1.1" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none"> <path d="M0,0 C40,33 66,52 75,52 C83,52 92,33 100,0 L100,100 L0,100 L0,0 Z" fill="#ffffff"></path> </svg> </li> <li> <svg class="lc-shape-divider-bottom" width="100%" style="height: 6vh;fill:white" viewBox="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none"> <path d="M0,0 C16.6666667,66 33.3333333,99 50,99 C66.6666667,99 83.3333333,66 100,0 L100,100 L0,100 L0,0 Z"></path> </svg> </li> <li> <svg class="lc-shape-divider-bottom" width="100%" style="height: 6vh;" version="1.1" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none"> <path d="M0,0 C6.83050094,50 15.1638343,75 25,75 C41.4957514,75 62.4956597,0 81.2456597,0 C93.7456597,0 99.9971065,0 100,0 L100,100 L0,100" fill="#FFFFFF"></path> </svg> </li> <li> <svg fill="white" class="lc-shape-divider-bottom" style="transform:rotate(-180deg);" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"> <path d="M421.9,6.5c22.6-2.5,51.5,0.4,75.5,5.3c23.6,4.9,70.9,23.5,100.5,35.7c75.8,32.2,133.7,44.5,192.6,49.7c23.6,2.1,48.7,3.5,103.4-2.5c54.7-6,106.2-25.6,106.2-25.6V0H0v30.3c0,0,72,32.6,158.4,30.5c39.2-0.7,92.8-6.7,134-22.4c21.2-8.1,52.2-18.2,79.7-24.2C399.3,7.9,411.6,7.5,421.9,6.5z"></path> </svg> </li> <li> <svg class="lc-shape-divider-bottom" style="fill:white; " xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1440 126"> <path d="M685.6,38.8C418.7-11.1,170.2,9.9,0,30v96h1440V30C1252.7,52.2,1010,99.4,685.6,38.8z"></path> </svg> </li> <li> <svg class="lc-shape-divider-bottom" style="fill:white; height: 6vh;" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 0 100 100" preserveAspectRatio="none"> <path d="M0 100 C 20 0 50 0 100 100 Z"></path> </svg> </li> </ul>`,
				},
				"Shadow": {
					property: "box-shadow",
					widget: "select",
					values: ["sm", "", "lg"],
					about: "Add or remove shadows to elements with box-shadow utilities.",
					docs: "https://getbootstrap.com/docs/5.3/utilities/shadows",
					class: "shadow"
				},
			},

		},
		"Animation": {
			"General": {
				"Animation Type": {
					target: "html",
					widget: "custom",
					about: "Enables animation using the Animate On Scroll library.",
					docs: "https://michalsnik.github.io/aos/",
					custom: `<select class="form-control" name="aos_animation_type_no" attribute-name="data-aos"> <option value="">None</option> <optgroup label="Fade animations"> <option value="fade">Fade</option> <option value="fade-up">Fade Up</option> <option value="fade-down">Fade Down</option> <option value="fade-left">Fade Left</option> <option value="fade-right">Fade Right</option> <option value="fade-up-right">Fade Up Right</option> <option value="fade-up-left">Fade Up Left</option> <option value="fade-down-right">Fade Down Right</option> <option value="fade-down-left">Fade Down Left</option> </optgroup> <optgroup label="Flip animations"> <option value="flip-up">Flip Up</option> <option value="flip-down">Flip Down</option> <option value="flip-left">Flip Left</option> <option value="flip-right">Flip Right</option> </optgroup> <optgroup label="Slide animations"> <option value="slide-up">Slide Up</option> <option value="slide-down">Slide Down</option> <option value="slide-left">Slide Left</option> <option value="slide-right">Slide Right</option> </optgroup> <optgroup label="Zoom animations"> <option value="zoom-in">Zoom In</option> <option value="zoom-in-up">Zoom In Up</option> <option value="zoom-in-down">Zoom In Down</option> <option value="zoom-in-left">Zoom In Left</option> <option value="zoom-in-right">Zoom In Right</option> <option value="zoom-out">Zoom Out</option> <option value="zoom-out-up">Zoom Out Up</option> <option value="zoom-out-down">Zoom Out Down</option> <option value="zoom-out-left">Zoom Out Left</option> <option value="zoom-out-right">Zoom Out Right</option> </optgroup> </select> `,
				},
				"Delay (ms)": {
					target: "html",
					widget: "custom",
					about: "Sets the delay of the animation play time. The duration value can be anywhere between 0 and 3000 with steps of 50ms. Since the duration is handled in CSS, using smaller steps or a wider range would have unnecessarily increased the size of the CSS code. The default value for this attribute is 0.",
					custom: `<input type="number" class="form-control" name="aos_delay" min="0" max="3000" step="50" attribute-name="data-aos-delay">`,
				},
				"Duration (ms)": {
					target: "html",
					widget: "custom",
					about: "Sets the duration of the animation. The duration value can be anywhere between 50 and 3000 with steps of 50ms. Since the duration is handled in CSS, using smaller steps or a wider range would have unnecessarily increased the size of the CSS code. This range should be sufficient for almost all animations. The default value for this attribute is 400.",
					custom: `<input type="number" class="form-control" name="aos_duration" min="0" max="3000" step="50" attribute-name="data-aos-duration">`,
				},
				"Easing": {
					target: "html",
					widget: "custom",
					about: " Use this attribute to control the timing function of the animation.",
					custom: `<select class="form-control" name="aos_easing" attribute-name="data-aos-easing"> <option value=""></option> <option value="linear">linear</option> <option value="ease">ease</option> <option value="ease-in">ease-in</option> <option value="ease-out">ease-out</option> <option value="ease-in-out">ease-in-out</option> <option value="ease-in-back">ease-in-back</option> <option value="ease-out-back">ease-out-back</option> <option value="ease-in-out-back">ease-in-out-back</option> <option value="ease-in-sine">ease-in-sine</option> <option value="ease-out-sine">ease-out-sine</option> <option value="ease-in-out-sine">ease-in-out-sine</option> <option value="ease-in-quad">ease-in-quad</option> <option value="ease-out-quad">ease-out-quad</option> <option value="ease-in-out-quad">ease-in-out-quad</option> <option value="ease-in-cubic">ease-in-cubic</option> <option value="ease-out-cubic">ease-out-cubic</option> <option value="ease-in-out-cubic">ease-in-out-cubic</option> <option value="ease-in-quart">ease-in-quart</option> <option value="ease-out-quart">ease-out-quart</option> <option value="ease-in-out-quart">ease-in-out-quart</option> </select>`,
				},
				"Once": {
					target: "html",
					widget: "custom",
					about: "By default, the animations are replayed every time the elements scroll into view. You can set the value of this attribute to true in order to animate the elements only once.",
					custom: `<select class="form-control" name="aos_once" attribute-name="data-aos-once"> <option value="false">False</option>	<option value="true">True</option>	</select>`,
				},
				"Mirror": {
					target: "html",
					widget: "custom",
					about: "Prevents the animation from disappearing when scrolling back up the page, so once the animations appear when you scroll down they will not animate on scroll up.",
					custom: `<select class="form-control" name="aos_mirror" attribute-name="data-aos-mirror">	<option value="">No</option>	<option value="true">Mirror</option></select>`,
				},
				"Offset (px)": {
					target: "html",
					attribute: "data-aos-offset",
					widget: "custom",
					custom: `<input type="number" class="form-control" name="aos_duration" min="0" max="9999" step="1" attribute-name="data-aos-offset">`,
					class: '', //the prefix
					about: "Use this attribute to trigger the animation sooner or later than the designated time. Its default value is 120px.",
				},





			},

		},

	},
	layout_elements: {
		"Main": {
			selector: "main > section",

		},
		"Container": {
			selector: "main .container, main .container-fluid, main .container-sm,main .container-md,main .container-lg,main .container-xl",


		},
		"Row": {
			selector: "main .row",

		},
		"Column": {
			selector: "main *[class^='col-'], main *[class*=' col-'],main .col",
			/*
			get demo_dyn_prop() {
				return (theFramework.properties.Size.Column);
			}
			*/

		},
		"Block": {
			selector: "main .lc-block",
		},

	},

};

//DEFINE INTRO PANELS STRUCTURE DYNAMICALLY recalling bits and bobs
function getCompleteProperties(layoutElementName, thePropertiesOriginal = theFramework.properties) {

	//deep clone config obj
	let theProperties = JSON.parse(JSON.stringify(thePropertiesOriginal));

	//define intro panel for columns
	if (layoutElementName.includes('Column')) {

		//add element to object before all others
		theProperties = Object.assign({}, {
			'Column': {
				'Column': (theProperties['Sizing']['Column']),
			}
		}, theProperties);
	}

	//define intro panel for containers
	if (layoutElementName.includes('Container')) {

		//add element to object before all others
		theProperties = Object.assign({}, {
			'Container': {
				'Container': (theProperties['Sizing']['Container']),
			}
		}, theProperties);
	}

	return theProperties;
}

//DEFINE BLOCKS
let theBlocks = {
	"Basic": {
		description: "Foundational typography and content blocks.",
		blocks: [
			{
				name: "Heading",
				icon_html: `<i aria-hidden="true" class="fa fa-header"></i>`,
				template_html: `<div editable="rich"><h2 class="text-5xl font-bold">The quick brown fox jumps over the lazy dog</h2></div>`
			},
			{
				name: "Single paragraph",
				icon_html: `<i aria-hidden="true" class="fa fa-align-left"></i>`,
				template_html: `<div editable="rich"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc et metus id ligula malesuada placerat sit amet quis enim. Aliquam erat volutpat. In pellentesque scelerisque auctor. Ut porta lacus eget nisi fermentum lobortis. Vestibulum facilisis tempor ipsum, ut rhoncus magna ultricies laoreet. Proin vehicula erat eget libero accumsan iaculis. </p></div>`
			},
			{
				name: "Rich Text Area",
				icon_html: `<i aria-hidden="true" class="fa fa-header"></i>`,
				template_html: `<div editable="rich"><h2 class="font-bold text-3xl">The quick brown fox jumps over the lazy dog</h2><p><strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit. Suspendisse a lacus est. Etiam diam metus, lobortis non augue at, placerat viverra risus. Cras ornare faucibus laoreet. Aenean vel nisi in ipsum congue fermentum et ut arcu. Proin leo diam, vulputate eu tellus ac, mattis cursus nunc. In aliquet erat ac eros congue maximus. Fusce cursus leo at elit tincidunt, consequat ultrices ante pretium. Vivamus ut dapibus nisl, nec condimentum purus.</p></div>`
			},
			{
				name: "Horizontal Ruler",
				icon_html: `<i class="fa fa-minus" aria-hidden="true"></i>`,
				template_html: `<hr class="my-4 border-base-300"/>`
			},
			{
				name: "Image",
				icon_html: `<i aria-hidden="true" class="fa fa-file-image-o"></i>`,
				template_html: `<img class="max-w-full h-auto" src="https://images.unsplash.com/photo-1503624886539-b1355ee1a745?ixlib=rb-0.3.5&amp;q=80&amp;fm=jpg&amp;crop=entropy&amp;cs=tinysrgb&amp;w=1080&amp;fit=max&amp;ixid=eyJhcHBfaWQiOjM3ODR9&amp;s=05d1b7e1db6bcbdc11c57c357768c11c"/>`
			},
			{
				name: "Blockquote",
				icon_html: `<i aria-hidden="true" class="fa fa-quote-right"></i>`,
				template_html: `<figure class="max-w-xl"> <blockquote class="border-l-4 border-primary pl-4 italic text-base-content"> <p editable="inline">A well-known quote, contained in a blockquote element.</p> </blockquote> <figcaption class="mt-2 text-sm text-base-content/70"> <span editable="inline">Someone famous in</span> <cite editable="inline">Source Title</cite> </figcaption> </figure>`
			},

			{
				name: "SVG Icon",
				icon_html: `<i aria-hidden="true" class="fa fa-star"></i>`,
				template_html: `<svg fill="currentColor" height="3em" lc-helper="svg-icon" viewbox="0 0 16 16" width="3em" xmlns="http://www.w3.org/2000/svg"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.283.95l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"></path></svg>`
			},

			{
				name: "Button",
				icon_html: `<i aria-hidden="true" class="fa fa-dot-circle-o"></i>`,
				template_html: `<a class="btn btn-primary" href="#" role="button">Click me, I'm a button</a>`
			},
			{
				name: "Button with Icon",
				icon_html: `<i aria-hidden="true" class="fa fa-arrow-right"></i>`,
				template_html: `<a class="btn btn-primary gap-2" href="#"> Read more <svg class="w-4 h-4" fill="currentColor" height="16" viewbox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" fill-rule="evenodd"></path></svg> </a>`
			},

			{
				name: "Whatsapp Button",
				icon_html: `<i aria-hidden="true" class="fa fa-arrow-right"></i>`,
				template_html: ` 
					<a class="btn btn-success gap-2" href="https://wa.me/13052024238"> Chat with us  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" class="bi" viewBox="0 0 16 16" lc-helper="svg-icon">
							<path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"></path>
						</svg></a>
				`
			},

		],
	},
	"Video / Media / Embed": {
		description: "Embeds for videos, audio, maps, and other rich media.",
		blocks: [
			{
				name: "YouTube",
				icon_html: `<i aria-hidden="true" class="fa fa-youtube-play"></i>`,
				template_html: `
				<figure class="aspect-video w-full overflow-hidden rounded shadow-xl embed-responsive">
					<iframe class="h-full w-full" src="https://www.youtube.com/embed/zpOULjyy-n8?rel=0" loading="lazy" allowfullscreen title="YouTube video"></iframe>
				</figure>
				`
			},
			{
				name: "MP4 Video",
				icon_html: `<i aria-hidden="true" class="fa fa-play-circle"></i>`,
				template_html: `
				<figure class="aspect-video w-full overflow-hidden rounded shadow-xl">
					<video class="h-full w-full object-cover" autoplay muted loop playsinline controls poster="https://images.unsplash.com/photo-1470104240373-bc1812eddc9f?auto=format&fit=crop&w=1200&q=80">
						<source src="https://cdn.livecanvas.com/media/nature/video.mp4" type="video/mp4">
					</video>
				</figure>
				`
			},
			{
				name: "Generic Iframe",
				icon_html: `<i aria-hidden="true" class="fa fa-code"></i>`,
				template_html: `
				<figure class="aspect-video w-full overflow-hidden rounded shadow-xl embed-responsive">
					<iframe class="h-full w-full" src="http://daisyui.com/" loading="lazy" title="Embedded content"></iframe>
				</figure>
				`
			},
			{
				name: "Lightbox Player",
				icon_html: `<i aria-hidden="true" class="fa fa-video-camera"></i>`,
				template_html: `
				<figure class="relative aspect-video w-full overflow-hidden rounded shadow-xl">
					<img class="h-full w-full object-cover" src="https://images.unsplash.com/photo-1621947081720-86970823b77a?auto=format&fit=crop&w=1400&q=80" alt="Video cover" loading="lazy">
					<a class="btn btn-circle btn-primary absolute inset-0 m-auto h-18 w-18 flex" href="https://www.youtube.com/watch?v=BKgpLOUYZJ4" aria-label="Play video">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="h-8 w-8" viewBox="0 0 24 24" lc-helper="svg-icon" fill="currentColor">
							<path d="M8.5,8.64L13.77,12L8.5,15.36V8.64M6.5,5V19L17.5,12"></path>
						</svg>
					</a>
				</figure>
				`
			},
			{
				name: "Vimeo",
				icon_html: `<i aria-hidden="true" class="fa fa-vimeo"></i>`,
				template_html: `
				<figure class="aspect-video w-full overflow-hidden rounded shadow-xl embed-responsive">
					<iframe class="h-full w-full" loading="lazy" src="https://player.vimeo.com/video/183213801?color=22d3ee&title=0&byline=0&portrait=0" title="Vimeo video"></iframe>
				</figure>
				`
			},
			{
				name: "SoundCloud",
				icon_html: `<i aria-hidden="true" class="fa fa-soundcloud"></i>`,
				template_html: `
				<figure class="w-full overflow-hidden rounded shadow-xl embed-responsive">
					<iframe class="h-128 w-full" loading="lazy" src="https://w.soundcloud.com/player/?visual=true&url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F564062355&show_artwork=true&dnt=1" title="SoundCloud player"></iframe>
				</figure>
				`
			},
			{
				name: "Mixcloud",
				icon_html: `<i aria-hidden="true" class="fa fa-mixcloud"></i>`,
				template_html: `
				<figure class="w-full overflow-hidden rounded shadow-xl embed-responsive">
					<iframe class="h-30 w-full" loading="lazy" src="https://www.mixcloud.com/widget/iframe/?feed=https%3A%2F%2Fwww.mixcloud.com%2FDJJazzyJeff%2Fsummertime-mixtape-vol-9%2F&hide_cover=1" title="Mixcloud player"></iframe>
				</figure>
				`
			},
			{
				name: "Google Map",
				icon_html: `<i aria-hidden="true" class="fa fa-map-marker"></i>`,
				template_html: `
				<figure class="aspect-video w-full overflow-hidden rounded shadow-xl " lc-helper="gmap-embed">
					<iframe class="h-full w-full" loading="lazy" src="https://maps.google.com/maps?q=London%2C%20UK&t=m&z=8&output=embed&iwloc=near" title="Google map"></iframe>
				</figure>
				`
			},
			{
				name: "Instagram Post",
				icon_html: `<i aria-hidden="true" class="fa fa-instagram"></i>`,
				template_html: `
				<!-- Replace "DPeNXh7gBOt" with last parameter in your Instagram URL -->
				<!-- lc-needs-hard-refresh -->

				<blockquote class="instagram-media" data-instgrm-captioned="" data-instgrm-permalink="https://www.instagram.com/p/DPeNXh7gBOt/?utm_source=ig_embed&amp;utm_campaign=loading" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);">
					<div style="padding:16px;"> <a href="https://www.instagram.com/p/DPeNXh7gBOt/?utm_source=ig_embed&amp;utm_campaign=loading" style=" background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%;" target="_blank">
							<div style=" display: flex; flex-direction: row; align-items: center;">
								<div style="background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 40px; margin-right: 14px; width: 40px;"></div>
								<div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center;">
									<div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 100px;"></div>
									<div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 60px;"></div>
								</div>
							</div>
							<div style="padding: 19% 0;"></div>
							<div style="display:block; height:50px; margin:0 auto 12px; width:50px;"><svg width="50px" height="50px" viewBox="0 0 60 60" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink">
									<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
										<g transform="translate(-511.000000, -20.000000)" fill="#000000">
											<g>
												<path d="M556.869,30.41 C554.814,30.41 553.148,32.076 553.148,34.131 C553.148,36.186 554.814,37.852 556.869,37.852 C558.924,37.852 560.59,36.186 560.59,34.131 C560.59,32.076 558.924,30.41 556.869,30.41 M541,60.657 C535.114,60.657 530.342,55.887 530.342,50 C530.342,44.114 535.114,39.342 541,39.342 C546.887,39.342 551.658,44.114 551.658,50 C551.658,55.887 546.887,60.657 541,60.657 M541,33.886 C532.1,33.886 524.886,41.1 524.886,50 C524.886,58.899 532.1,66.113 541,66.113 C549.9,66.113 557.115,58.899 557.115,50 C557.115,41.1 549.9,33.886 541,33.886 M565.378,62.101 C565.244,65.022 564.756,66.606 564.346,67.663 C563.803,69.06 563.154,70.057 562.106,71.106 C561.058,72.155 560.06,72.803 558.662,73.347 C557.607,73.757 556.021,74.244 553.102,74.378 C549.944,74.521 548.997,74.552 541,74.552 C533.003,74.552 532.056,74.521 528.898,74.378 C525.979,74.244 524.393,73.757 523.338,73.347 C521.94,72.803 520.942,72.155 519.894,71.106 C518.846,70.057 518.197,69.06 517.654,67.663 C517.244,66.606 516.755,65.022 516.623,62.101 C516.479,58.943 516.448,57.996 516.448,50 C516.448,42.003 516.479,41.056 516.623,37.899 C516.755,34.978 517.244,33.391 517.654,32.338 C518.197,30.938 518.846,29.942 519.894,28.894 C520.942,27.846 521.94,27.196 523.338,26.654 C524.393,26.244 525.979,25.756 528.898,25.623 C532.057,25.479 533.004,25.448 541,25.448 C548.997,25.448 549.943,25.479 553.102,25.623 C556.021,25.756 557.607,26.244 558.662,26.654 C560.06,27.196 561.058,27.846 562.106,28.894 C563.154,29.942 563.803,30.938 564.346,32.338 C564.756,33.391 565.244,34.978 565.378,37.899 C565.522,41.056 565.552,42.003 565.552,50 C565.552,57.996 565.522,58.943 565.378,62.101 M570.82,37.631 C570.674,34.438 570.167,32.258 569.425,30.349 C568.659,28.377 567.633,26.702 565.965,25.035 C564.297,23.368 562.623,22.342 560.652,21.575 C558.743,20.834 556.562,20.326 553.369,20.18 C550.169,20.033 549.148,20 541,20 C532.853,20 531.831,20.033 528.631,20.18 C525.438,20.326 523.257,20.834 521.349,21.575 C519.376,22.342 517.703,23.368 516.035,25.035 C514.368,26.702 513.342,28.377 512.574,30.349 C511.834,32.258 511.326,34.438 511.181,37.631 C511.035,40.831 511,41.851 511,50 C511,58.147 511.035,59.17 511.181,62.369 C511.326,65.562 511.834,67.743 512.574,69.651 C513.342,71.625 514.368,73.296 516.035,74.965 C517.703,76.634 519.376,77.658 521.349,78.425 C523.257,79.167 525.438,79.673 528.631,79.82 C531.831,79.965 532.853,80.001 541,80.001 C549.148,80.001 550.169,79.965 553.369,79.82 C556.562,79.673 558.743,79.167 560.652,78.425 C562.623,77.658 564.297,76.634 565.965,74.965 C567.633,73.296 568.659,71.625 569.425,69.651 C570.167,67.743 570.674,65.562 570.82,62.369 C570.966,59.17 571,58.147 571,50 C571,41.851 570.966,40.831 570.82,37.631"></path>
											</g>
										</g>
									</g>
								</svg></div>
							<div style="padding-top: 8px;">
								<div style=" color:#3897f0; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:550; line-height:18px;">View this post on Instagram</div>
							</div>
							<div style="padding: 12.5% 0;"></div>
							<div style="display: flex; flex-direction: row; margin-bottom: 14px; align-items: center;">
								<div>
									<div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(0px) translateY(7px);"></div>
									<div style="background-color: #F4F4F4; height: 12.5px; transform: rotate(-45deg) translateX(3px) translateY(1px); width: 12.5px; flex-grow: 0; margin-right: 14px; margin-left: 2px;"></div>
									<div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(9px) translateY(-18px);"></div>
								</div>
								<div style="margin-left: 8px;">
									<div style=" background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 20px; width: 20px;"></div>
									<div style=" width: 0; height: 0; border-top: 2px solid transparent; border-left: 6px solid #f4f4f4; border-bottom: 2px solid transparent; transform: translateX(16px) translateY(-4px) rotate(30deg)"></div>
								</div>
								<div style="margin-left: auto;">
									<div style=" width: 0px; border-top: 8px solid #F4F4F4; border-right: 8px solid transparent; transform: translateY(16px);"></div>
									<div style=" background-color: #F4F4F4; flex-grow: 0; height: 12px; width: 16px; transform: translateY(-4px);"></div>
									<div style=" width: 0; height: 0; border-top: 8px solid #F4F4F4; border-left: 8px solid transparent; transform: translateY(-4px) translateX(8px);"></div>
								</div>
							</div>
							<div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center; margin-bottom: 24px;">
								<div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 224px;"></div>
								<div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 144px;"></div>
							</div>
						</a>

					</div>
				</blockquote>
				<script async="" src="//www.instagram.com/embed.js"></script>
				`
			}
		]
	},
	"Buttons": {
	description: "Button components built with Tailwind CSS and daisyUI.",
	blocks: 	[
		{
			name: "Button",
			icon_html: `<i aria-hidden="true" class="fa fa-hand-pointer-o"></i>`,
			template_html: `<button class="btn btn-primary ">Default</button>`
		},
		{
			name: "Button With Icon",
			icon_html: `<i aria-hidden="true" class="fa fa-arrow-right"></i>`,
			template_html: `<button class="btn btn-primary">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-[1.2em]"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
							Like
							</button>`
		},
		{
			name: "Button Outline",
			icon_html: `<i aria-hidden="true" class="fa fa-hand-pointer-o"></i>`,
			template_html: `<button class="btn btn-outline btn-primary">Primary</button>`
		},
		{
			name: "Button – Soft",
			icon_html: `<i aria-hidden="true" class="fa fa-hand-pointer-o"></i>`,
			template_html: `<button class="btn btn-soft btn-primary">Soft Primary</button>`
		},
		{
			name: "Button Ghost",
			icon_html: `<i aria-hidden="true" class="fa fa-hand-pointer-o"></i>`,
			template_html: `<button class="btn btn-primary btn-ghost">Ghost</button>`
		},
		{
			name: "Button Link",
			icon_html: `<i aria-hidden="true" class="fa fa-link"></i>`,
			template_html: `<button class="btn btn-link">Link</button>`
		},


		{
			name: "Button Tiny",
			icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
			template_html: `<button class="btn btn-xs btn-primary">Tiny</button>`
		},
		{
			name: "Button Small",
			icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
			template_html: `<button class="btn btn-sm btn-primary">Small</button>`
		},
		{
			name: "Button Normal",
			icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
			template_html: `
					<button class="btn btn-primary">Normal</button>
			`
		},
		{
			name: "Button Large",
			icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
			template_html: `
					<button class="btn btn-lg btn-primary">Large</button>
			`
		},
		{
			name: "Button Block",
			icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
			template_html: `
					<button class="btn btn-block btn-primary">Block</button>
			`
		},
		{
			name: "Button Wide",
			icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
			template_html: `<button class="btn btn-wide btn-primary">Wide</button>`
		},
		{
			name: "Button Square",
			icon_html: `<i aria-hidden="true" class="fa fa-square"></i>`,
			template_html: `<button class="btn btn-square btn-primary">
  								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-[1.2em]"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
							</button>`
		},
	
		{
			name: "Button Circle",
			icon_html: `<i aria-hidden="true" class="fa fa-circle"></i>`,
			template_html: `<button class="btn btn-circle btn-primary">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-[1.2em]"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
							</button>`
		},

		

		
		
	
		{
			name: "Colors - All Buttons",
			icon_html: `<i aria-hidden="true" class="fa fa-paint-brush"></i>`,
			template_html: `
				<div class="flex flex-wrap gap-2">
					<button class="btn">Default</button>
					<button class="btn btn-primary">Primary</button>
					<button class="btn btn-secondary">Secondary</button>
					<button class="btn btn-accent">Accent</button>
					<button class="btn btn-info">Info</button>
					<button class="btn btn-success">Success</button>
					<button class="btn btn-warning">Warning</button>
					<button class="btn btn-error">Error</button>
				</div>
			`
		},
		{
			name: "All Colors Outline - Buttons",
			icon_html: `<i aria-hidden="true" class="fa fa-square-o"></i>`,
			template_html: `
				<div class="flex flex-wrap gap-2">
					<button class="btn btn-outline">Default</button>
					<button class="btn btn-outline btn-primary">Primary</button>
					<button class="btn btn-outline btn-secondary">Secondary</button>
					<button class="btn btn-outline btn-accent">Accent</button>
					<button class="btn btn-outline btn-info">Info</button>
					<button class="btn btn-outline btn-success">Success</button>
					<button class="btn btn-outline btn-warning">Warning</button>
					<button class="btn btn-outline btn-error">Error</button>
				</div>
			`
		},
		
		{
			name: "All Soft - Buttons",
			icon_html: `<i aria-hidden="true" class="fa fa-square-o"></i>`,
			template_html: `
				<div class="flex flex-wrap gap-2">
					<button class="btn btn-soft">Default</button>
					<button class="btn btn-soft btn-primary">Primary</button>
					<button class="btn btn-soft btn-secondary">Secondary</button>
					<button class="btn btn-soft btn-accent">Accent</button>
					<button class="btn btn-soft btn-info">Info</button>
					<button class="btn btn-soft btn-success">Success</button>
					<button class="btn btn-soft btn-warning">Warning</button>
					<button class="btn btn-soft btn-error">Error</button>
				</div>
			`
		},

		{
			name: "All Sizes Buttons",
			icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
			template_html: `
				<div class="flex flex-wrap gap-2 items-center">
					<button class="btn btn-xs btn-primary">Tiny</button>
					<button class="btn btn-sm btn-primary">Small</button>
					<button class="btn btn-primary">Normal</button>
					<button class="btn btn-lg btn-primary">Large</button>
				</div>
			`
		},
		{
			name: "All States -	Buttons",
			icon_html: `<i aria-hidden="true" class="fa fa-toggle-on"></i>`,
			template_html: `
				<div class="flex flex-wrap gap-2">
					<button class="btn btn-primary btn-active">Active</button>
					<button class="btn btn-primary" disabled>Disabled</button>
					<button class="btn btn-primary">
						<span class="loading loading-spinner"></span>
						Loading
					</button>
				</div>
			`
		}

		]
	},

	"Avatar": {
		description: "Avatar component examples from DaisyUI.",
		blocks: [

			{
				name: "Avatar",
				icon_html: `<i aria-hidden="true" class="fa fa-user"></i>`,
				template_html: `
				<div class="avatar">
					<div class="w-16 rounded">
						<img src="https://img.daisyui.com/images/profile/demo/superperson@192.webp">
					</div>
				</div>
				`
			},

			{
				name: "Avatar - Circle",
				icon_html: `<i aria-hidden="true" class="fa fa-user-circle"></i>`,
				template_html: `
				<div class="avatar">
					<div class="w-16 rounded-full">
						 <img src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp" />
					</div>
				</div>
				`
			},
			{
				name: "Avatar - Ring",
				icon_html: `<i aria-hidden="true" class="fa fa-circle-o"></i>`,
				template_html: `
				<div class="avatar">
					<div class="w-16 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
						<img src="https://i.pravatar.cc/300" alt="Avatar" loading="lazy">
					</div>
				</div>
				`
			},

			{
				name: "Avatar - All Sizes",
				icon_html: `<i aria-hidden="true" class="fa fa-user"></i>`,
				template_html: `
				<div class="avatar">
					<div class="w-32 rounded">
						<img src="https://img.daisyui.com/images/profile/demo/superperson@192.webp" />
					</div>
					</div>
					<div class="avatar">
					<div class="w-20 rounded">
						<img
						src="https://img.daisyui.com/images/profile/demo/superperson@192.webp"
						alt="Tailwind-CSS-Avatar-component"
						/>
					</div>
					</div>
					<div class="avatar">
					<div class="w-16 rounded">
						<img
						src="https://img.daisyui.com/images/profile/demo/superperson@192.webp"
						alt="Tailwind-CSS-Avatar-component"
						/>
					</div>
					</div>
					<div class="avatar">
					<div class="w-8 rounded">
						<img
						src="https://img.daisyui.com/images/profile/demo/superperson@192.webp"
						alt="Tailwind-CSS-Avatar-component"
						/>
					</div>
				</div>`
			},

			{
				name: "Avatar - Group",
				icon_html: `<i aria-hidden="true" class="fa fa-users"></i>`,
				template_html: `
				 <div class="avatar-group -space-x-6">
					<div class="avatar">
						<div class="w-12">
						<img src="https://img.daisyui.com/images/profile/demo/batperson@192.webp" />
						</div>
					</div>
					<div class="avatar">
						<div class="w-12">
						<img src="https://img.daisyui.com/images/profile/demo/spiderperson@192.webp" />
						</div>
					</div>
					<div class="avatar">
						<div class="w-12">
						<img src="https://img.daisyui.com/images/profile/demo/averagebulk@192.webp" />
						</div>
					</div>
					<div class="avatar">
						<div class="w-12">
						<img src="https://img.daisyui.com/images/profile/demo/wonderperson@192.webp" />
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Avatar - Group with Counter",
				icon_html: `<i aria-hidden="true" class="fa fa-font"></i>`,
				template_html: `
				<div class="avatar-group -space-x-6">
					<div class="avatar">
						<div class="w-12">
						<img src="https://img.daisyui.com/images/profile/demo/batperson@192.webp" />
						</div>
					</div>
					<div class="avatar">
						<div class="w-12">
						<img src="https://img.daisyui.com/images/profile/demo/spiderperson@192.webp" />
						</div>
					</div>
					<div class="avatar">
						<div class="w-12">
						<img src="https://img.daisyui.com/images/profile/demo/averagebulk@192.webp" />
						</div>
					</div>
					<div class="avatar avatar-placeholder">
						<div class="bg-neutral text-neutral-content w-12">
						<span>+99</span>
						</div>
					</div>
				</div>
				`
			}
		]
	},

	"Badges": {
		description: "Badge variations showcasing DaisyUI styles and behaviors.",
		blocks: [

			{
				name: "Badge",
				icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
				template_html: `<span editable="inline" class="badge badge-primary">Base</span>`
			},

			{
				name: "Badge soft",
				icon_html: `<i aria-hidden="true" class="fa fa-sliders"></i>`,
				template_html: `<span editable="inline" class="badge badge-primary badge-soft">Primary</span>`
			},
			{
				name: "Badge outline",
				icon_html: `<i aria-hidden="true" class="fa fa-square-o"></i>`,
				template_html: `<span editable="inline" class="badge badge-outline badge-primary">Primary</span>`
			},


			{
				name: "All Badge sizes",
				icon_html: `<i aria-hidden="true" class="fa fa-text-height"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2 items-center">
					<span editable="inline" class="badge badge-xs badge-primary">XS</span>
					<span editable="inline" class="badge badge-sm badge-primary">SM</span>
					<span editable="inline" class="badge badge-primary">Base</span>
					<span editable="inline" class="badge badge-lg badge-primary">LG</span>
				</div>
			`
			},

			{
				name: "All Badge with colors",
				icon_html: `<i aria-hidden="true" class="fa fa-paint-brush"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2">
					<span editable="inline" class="badge badge-neutral">Neutral</span>
					<span editable="inline" class="badge badge-primary">Primary</span>
					<span editable="inline" class="badge badge-secondary">Secondary</span>
					<span editable="inline" class="badge badge-accent">Accent</span>
					<span editable="inline" class="badge badge-info">Info</span>
					<span editable="inline" class="badge badge-success">Success</span>
					<span editable="inline" class="badge badge-warning">Warning</span>
					<span editable="inline" class="badge badge-error">Error</span>
				</div>
			`
			},

			{
				name: "All Badge with soft style",
				icon_html: `<i aria-hidden="true" class="fa fa-sliders"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2">
					<span editable="inline" class="badge badge-primary badge-soft">Primary</span>
					<span editable="inline" class="badge badge-secondary badge-soft">Secondary</span>
					<span editable="inline" class="badge badge-accent badge-soft">Accent</span>
					<span editable="inline" class="badge badge-neutral badge-soft">Neutral</span>
					<span editable="inline" class="badge badge-info badge-soft">Info</span>
					<span editable="inline" class="badge badge-success badge-soft">Success</span>
					<span editable="inline" class="badge badge-warning badge-soft">Warning</span>
					<span editable="inline" class="badge badge-error badge-soft">Error</span>
				</div>
			`
			},

			{
				name: "All Badge with icon",
				icon_html: `<i aria-hidden="true" class="fa fa-star"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2">
					<span class="badge badge-info gap-2">
						<svg class="size-[1em]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="currentColor" stroke-linejoin="miter" stroke-linecap="butt"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-linecap="square" stroke-miterlimit="10" stroke-width="2"></circle><path d="m12,17v-5.5c0-.276-.224-.5-.5-.5h-1.5" fill="none" stroke="currentColor" stroke-linecap="square" stroke-miterlimit="10" stroke-width="2"></path><circle cx="12" cy="7.25" r="1.25" fill="currentColor" stroke-width="2"></circle></g></svg>
						Info
					</span>
					<span class="badge badge-success gap-2">
						<svg class="size-[1em]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="currentColor" stroke-linejoin="miter" stroke-linecap="butt"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-linecap="square" stroke-miterlimit="10" stroke-width="2"></circle><polyline points="7 13 10 16 17 8" fill="none" stroke="currentColor" stroke-linecap="square" stroke-miterlimit="10" stroke-width="2"></polyline></g></svg>
						Success
					</span>
					<span class="badge badge-warning gap-2">
						<svg class="size-[1em]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18"><g fill="currentColor"><path d="M7.638,3.495L2.213,12.891c-.605,1.048,.151,2.359,1.362,2.359H14.425c1.211,0,1.967-1.31,1.362-2.359L10.362,3.495c-.605-1.048-2.119-1.048-2.724,0Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path><line x1="9" y1="6.5" x2="9" y2="10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></line><path d="M9,13.569c-.552,0-1-.449-1-1s.448-1,1-1,1,.449,1,1-.448,1-1,1Z" fill="currentColor" data-stroke="none" stroke="none"></path></g></svg>
						Warning
					</span>
					<span class="badge badge-error gap-2">
						<svg class="size-[1em]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="currentColor"><rect x="1.972" y="11" width="20.056" height="2" transform="translate(-4.971 12) rotate(-45)" fill="currentColor" stroke-width="0"></rect><path d="m12,23c-6.065,0-11-4.935-11-11S5.935,1,12,1s11,4.935,11,11-4.935,11-11,11Zm0-20C7.038,3,3,7.037,3,12s4.038,9,9,9,9-4.037,9-9S16.962,3,12,3Z" stroke-width="0" fill="currentColor"></path></g></svg>
						Error
					</span>
				</div>
			`
			},

			{
				name: "all Badge with outline style",
				icon_html: `<i aria-hidden="true" class="fa fa-square-o"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2">
					<span editable="inline" class="badge badge-outline">Default</span>
					<span editable="inline" class="badge badge-outline badge-primary">Primary</span>
					<span editable="inline" class="badge badge-outline badge-secondary">Secondary</span>
					<span editable="inline" class="badge badge-outline badge-accent">Accent</span>
					<span editable="inline" class="badge badge-outline badge-neutral">Neutral</span>
					<span editable="inline" class="badge badge-outline badge-info">Info</span>
					<span editable="inline" class="badge badge-outline badge-success">Success</span>
					<span editable="inline" class="badge badge-outline badge-warning">Warning</span>
					<span editable="inline" class="badge badge-outline badge-error">Error</span>
				</div>
			`
			},

			{
				name: "All Badge with dash style",
				icon_html: `<i aria-hidden="true" class="fa fa-ellipsis-h"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2">
					<span editable="inline" class="badge badge-dash">Default</span>
					<span editable="inline" class="badge badge-dash badge-primary">Primary</span>
					<span editable="inline" class="badge badge-dash badge-secondary">Secondary</span>
					<span editable="inline" class="badge badge-dash badge-accent">Accent</span>
					<span editable="inline" class="badge badge-dash badge-neutral">Neutral</span>
					<span editable="inline" class="badge badge-dash badge-info">Info</span>
					<span editable="inline" class="badge badge-dash badge-success">Success</span>
					<span editable="inline" class="badge badge-dash badge-warning">Warning</span>
					<span editable="inline" class="badge badge-dash badge-error">Error</span>
				</div>
			`
			},

			{
				name: "Neutral badge with outline or dash style",
				icon_html: `<i aria-hidden="true" class="fa fa-adjust"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2">
					<span editable="inline" class="badge badge-neutral badge-outline">Neutral outline</span>
					<span editable="inline" class="badge badge-neutral badge-dash">Neutral dash</span>
				</div>
			`
			},

			{
				name: "Badge ghost",
				icon_html: `<i aria-hidden="true" class="fa fa-circle-o"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2">
					<span editable="inline" class="badge badge-ghost">Ghost</span>
				</div>
			`
			},

			

			{
				name: "Badge in a button",
				icon_html: `<i aria-hidden="true" class="fa fa-envelope"></i>`,
				template_html: `
				<div class="flex flex-wrap gap-2">
					<button class="btn btn-neutral gap-2">
						Inbox
						<span class="badge badge-sm badge-primary">4</span>
					</button>
					<button class="btn btn-outline gap-2">
						Notifications
						<span class="badge badge-xs badge-error">99+</span>
					</button>
				</div>
			`
			}
		]
	},

	"Carousel": {
		description: "some desc goes here",
		blocks: [
			{
				name: "Carousel - Snap Start",
				icon_html: `<i aria-hidden="true" class="fa fa-align-left"></i>`,
				template_html: `
				<div class="carousel rounded-box">
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1559703248-dcaaec9fab78.webp" alt="Burger" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1565098772267-60af42b81ef2.webp" alt="Burger" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1572635148818-ef6fd45eb394.webp" alt="Burger" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1494253109108-2e30c049369b.webp" alt="Burger" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1550258987-190a2d41a8ba.webp" alt="Burger" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1559181567-c3190ca9959b.webp" alt="Burger" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1601004890684-d8cbf643f5f2.webp" alt="Burger" />
					</div>
				</div>
				`
			},
			{
				name: "Carousel - Snap Center",
				icon_html: `<i aria-hidden="true" class="fa fa-align-center"></i>`,
				template_html: `
				<div class="carousel carousel-center rounded-box">
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1494253109108-2e30c049369b.webp" alt="Pizza" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1550258987-190a2d41a8ba.webp" alt="Pizza" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1559181567-c3190ca9959b.webp" alt="Pizza" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1601004890684-d8cbf643f5f2.webp" alt="Pizza" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1559703248-dcaaec9fab78.webp" alt="Pizza" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1565098772267-60af42b81ef2.webp" alt="Pizza" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1572635148818-ef6fd45eb394.webp" alt="Pizza" />
					</div>
					
				</div>
				`
			},
			{
				name: "Carousel - Snap End",
				icon_html: `<i aria-hidden="true" class="fa fa-align-right"></i>`,
				template_html: `
				<div class="carousel carousel-end rounded-box">
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1559181567-c3190ca9959b.webp" alt="Drink" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1601004890684-d8cbf643f5f2.webp" alt="Drink" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1559703248-dcaaec9fab78.webp" alt="Drink" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1565098772267-60af42b81ef2.webp" alt="Drink" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1572635148818-ef6fd45eb394.webp" alt="Drink" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1494253109108-2e30c049369b.webp" alt="Drink" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1550258987-190a2d41a8ba.webp" alt="Drink" />
					</div>
					
				</div>
				`
			},
			{
				name: "Carousel - Full Width Items",
				icon_html: `<i aria-hidden="true" class="fa fa-square-o"></i>`,
				template_html: `
				<div class="carousel rounded-box w-64">
					<div class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1559703248-dcaaec9fab78.webp" class="w-full" alt="Tailwind CSS Carousel component" />
					</div>
					<div class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1565098772267-60af42b81ef2.webp" class="w-full" alt="Tailwind CSS Carousel component" />
					</div>
					<div class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1572635148818-ef6fd45eb394.webp" class="w-full" alt="Tailwind CSS Carousel component" />
					</div>
					<div class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1494253109108-2e30c049369b.webp" class="w-full" alt="Tailwind CSS Carousel component" />
					</div>
					<div class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1550258987-190a2d41a8ba.webp" class="w-full" alt="Tailwind CSS Carousel component" />
					</div>
					<div class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1559181567-c3190ca9959b.webp" class="w-full" alt="Tailwind CSS Carousel component" />
					</div>
					<div class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1601004890684-d8cbf643f5f2.webp" class="w-full" alt="Tailwind CSS Carousel component" />
					</div>
				</div>
				`
			},
			{
				name: "Carousel - Vertical",
				icon_html: `<i aria-hidden="true" class="fa fa-arrows-v"></i>`,
				template_html: `
				<div class="carousel carousel-vertical rounded-box h-96">
					<div class="carousel-item h-full">
						<img src="https://img.daisyui.com/images/stock/photo-1559703248-dcaaec9fab78.webp" />
					</div>
					<div class="carousel-item h-full">
						<img src="https://img.daisyui.com/images/stock/photo-1565098772267-60af42b81ef2.webp" />
					</div>
					<div class="carousel-item h-full">
						<img src="https://img.daisyui.com/images/stock/photo-1572635148818-ef6fd45eb394.webp" />
					</div>
					<div class="carousel-item h-full">
						<img src="https://img.daisyui.com/images/stock/photo-1494253109108-2e30c049369b.webp" />
					</div>
					<div class="carousel-item h-full">
						<img src="https://img.daisyui.com/images/stock/photo-1550258987-190a2d41a8ba.webp" />
					</div>
					<div class="carousel-item h-full">
						<img src="https://img.daisyui.com/images/stock/photo-1559181567-c3190ca9959b.webp" />
					</div>
					<div class="carousel-item h-full">
						<img src="https://img.daisyui.com/images/stock/photo-1601004890684-d8cbf643f5f2.webp" />
					</div>
				</div>
				`
			},
			{
				name: "Carousel - Half Width Items",
				icon_html: `<i aria-hidden="true" class="fa fa-columns"></i>`,
				template_html: `
				<div class="carousel rounded-box w-96">
					<div class="carousel-item w-1/2">
						<img src="https://img.daisyui.com/images/stock/photo-1559703248-dcaaec9fab78.webp" class="w-full" />
					</div>
					<div class="carousel-item w-1/2">
						<img src="https://img.daisyui.com/images/stock/photo-1565098772267-60af42b81ef2.webp" class="w-full" />
					</div>
					<div class="carousel-item w-1/2">
						<img src="https://img.daisyui.com/images/stock/photo-1572635148818-ef6fd45eb394.webp" class="w-full" />
					</div>
					<div class="carousel-item w-1/2">
						<img src="https://img.daisyui.com/images/stock/photo-1494253109108-2e30c049369b.webp" class="w-full" />
					</div>
					<div class="carousel-item w-1/2">
						<img src="https://img.daisyui.com/images/stock/photo-1550258987-190a2d41a8ba.webp" class="w-full" />
					</div>
					<div class="carousel-item w-1/2">
						<img src="https://img.daisyui.com/images/stock/photo-1559181567-c3190ca9959b.webp" class="w-full" />
					</div>
					<div class="carousel-item w-1/2">
						<img src="https://img.daisyui.com/images/stock/photo-1601004890684-d8cbf643f5f2.webp" class="w-full" />
					</div>
				</div>
				`
			},
			{
				name: "Carousel - Full Bleed",
				icon_html: `<i aria-hidden="true" class="fa fa-image"></i>`,
				template_html: `
				<div class="carousel carousel-center bg-neutral rounded-box max-w-md space-x-4 p-4">
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1559703248-dcaaec9fab78.webp" class="rounded-box" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1565098772267-60af42b81ef2.webp" class="rounded-box" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1572635148818-ef6fd45eb394.webp" class="rounded-box" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1494253109108-2e30c049369b.webp" class="rounded-box" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1550258987-190a2d41a8ba.webp" class="rounded-box" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1559181567-c3190ca9959b.webp" class="rounded-box" />
					</div>
					<div class="carousel-item">
						<img src="https://img.daisyui.com/images/stock/photo-1601004890684-d8cbf643f5f2.webp" class="rounded-box" />
					</div>
				</div>
				`
			},
			{
				name: "Carousel - Indicator Buttons",
				icon_html: `<i aria-hidden="true" class="fa fa-ellipsis-h"></i>`,
				template_html: `
				<div class="carousel w-full">
					<div id="item1" class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1625726411847-8cbb60cc71e6.webp" class="w-full" />
					</div>
					<div id="item2" class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1609621838510-5ad474b7d25d.webp" class="w-full" />
					</div>
					<div id="item3" class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1414694762283-acccc27bca85.webp" class="w-full" />
					</div>
					<div id="item4" class="carousel-item w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1665553365602-b2fb8e5d1707.webp" class="w-full" />
					</div>
				</div>
				<div class="flex w-full justify-center gap-2 py-2">
					<a href="#item1" class="btn btn-xs">1</a>
					<a href="#item2" class="btn btn-xs">2</a>
					<a href="#item3" class="btn btn-xs">3</a>
					<a href="#item4" class="btn btn-xs">4</a>
				</div>
				`
			},
			{
				name: "Carousel - Prev / Next",
				icon_html: `<i aria-hidden="true" class="fa fa-arrows-h"></i>`,
				template_html: `
				<div class="carousel w-full">
					<div id="slide1" class="carousel-item relative w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1625726411847-8cbb60cc71e6.webp" class="w-full" />
						<div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
							<a href="#slide4" class="btn btn-circle">❮</a>
							<a href="#slide2" class="btn btn-circle">❯</a>
						</div>
					</div>
					<div id="slide2" class="carousel-item relative w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1609621838510-5ad474b7d25d.webp" class="w-full" />
						<div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
							<a href="#slide1" class="btn btn-circle">❮</a>
							<a href="#slide3" class="btn btn-circle">❯</a>
						</div>
					</div>
					<div id="slide3" class="carousel-item relative w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1414694762283-acccc27bca85.webp" class="w-full" />
						<div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
							<a href="#slide2" class="btn btn-circle">❮</a>
							<a href="#slide4" class="btn btn-circle">❯</a>
						</div>
					</div>
					<div id="slide4" class="carousel-item relative w-full">
						<img src="https://img.daisyui.com/images/stock/photo-1665553365602-b2fb8e5d1707.webp" class="w-full" />
						<div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
							<a href="#slide3" class="btn btn-circle">❮</a>
							<a href="#slide1" class="btn btn-circle">❯</a>
						</div>
					</div>
				</div>
				`
			}
		]
	},

	"Accordion": {
		description: "some desc goes here",
		blocks: [
				{
					name: "Accordion - Arrow Icon",
					icon_html: `<i aria-hidden="true" class="fa fa-angle-down"></i>`,
					template_html: `
					<div class="collapse collapse-arrow bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-2" checked="checked" />
						<div class="collapse-title font-semibold">How do I create an account?</div>
						<div class="collapse-content text-sm">Click the "Sign Up" button in the top right corner and follow the registration process.</div>
					</div>
					<div class="collapse collapse-arrow bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-2" />
						<div class="collapse-title font-semibold">I forgot my password. What should I do?</div>
						<div class="collapse-content text-sm">Click on "Forgot Password" on the login page and follow the instructions sent to your email.</div>
					</div>
					<div class="collapse collapse-arrow bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-2" />
						<div class="collapse-title font-semibold">How do I update my profile information?</div>
						<div class="collapse-content text-sm">Go to "My Account" settings and select "Edit Profile" to make changes.</div>
					</div>
					`
				},
			{
				name: "Accordion - Plus Minus",
				icon_html: `<i aria-hidden="true" class="fa fa-plus-square"></i>`,
				template_html: `
					<div class="collapse collapse-plus bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-3" checked="checked" />
						<div class="collapse-title font-semibold">How do I create an account?</div>
						<div class="collapse-content text-sm">Click the "Sign Up" button in the top right corner and follow the registration process.</div>
					</div>
					<div class="collapse collapse-plus bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-3" />
						<div class="collapse-title font-semibold">I forgot my password. What should I do?</div>
						<div class="collapse-content text-sm">Click on "Forgot Password" on the login page and follow the instructions sent to your email.</div>
					</div>
					<div class="collapse collapse-plus bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-3" />
						<div class="collapse-title font-semibold">How do I update my profile information?</div>
						<div class="collapse-content text-sm">Go to "My Account" settings and select "Edit Profile" to make changes.</div>
					</div>
					`
				},
				{
					name: "Accordion - Radio Inputs",
					icon_html: `<i aria-hidden="true" class="fa fa-dot-circle-o"></i>`,
					template_html: `
					<div class="collapse bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-1" checked="checked" />
						<div class="collapse-title font-semibold">How do I create an account?</div>
						<div class="collapse-content text-sm">Click the "Sign Up" button in the top right corner and follow the registration process.</div>
					</div>
					<div class="collapse bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-1" />
						<div class="collapse-title font-semibold">I forgot my password. What should I do?</div>
						<div class="collapse-content text-sm">Click on "Forgot Password" on the login page and follow the instructions sent to your email.</div>
					</div>
					<div class="collapse bg-base-100 border border-base-300">
						<input type="radio" name="my-accordion-1" />
						<div class="collapse-title font-semibold">How do I update my profile information?</div>
						<div class="collapse-content text-sm">Go to "My Account" settings and select "Edit Profile" to make changes.</div>
					</div>
					`
				},
				{
					name: "Accordion - Details",
					icon_html: `<i aria-hidden="true" class="fa fa-bars"></i>`,
					template_html: `
					<details class="collapse bg-base-100 border border-base-300" name="my-accordion-det-1" open>
						<summary class="collapse-title font-semibold">How do I create an account?</summary>
						<div class="collapse-content text-sm">Click the "Sign Up" button in the top right corner and follow the registration process.</div>
					</details>
					<details class="collapse bg-base-100 border border-base-300" name="my-accordion-det-1">
						<summary class="collapse-title font-semibold">I forgot my password. What should I do?</summary>
						<div class="collapse-content text-sm">Click on "Forgot Password" on the login page and follow the instructions sent to your email.</div>
					</details>
					<details class="collapse bg-base-100 border border-base-300" name="my-accordion-det-1">
						<summary class="collapse-title font-semibold">How do I update my profile information?</summary>
						<div class="collapse-content text-sm">Go to "My Account" settings and select "Edit Profile" to make changes.</div>
					</details>
					`
				}
				
			]
		},
	 

	"Countdown (latest version needed)": {
		description: "some desc goes here",
		blocks: [
			{
				name: "Countdown - Simple",
				icon_html: `<i aria-hidden="true" class="fa fa-clock-o"></i>`,
				template_html: `
				<span class="countdown">
					<span style="--value:59;" aria-live="polite" aria-label="59">59</span>
				</span>
				`
			},
			{
				name: "Countdown - Large 2 Digits",
				icon_html: `<i aria-hidden="true" class="fa fa-hourglass-half"></i>`,
				template_html: `
				<span class="countdown font-mono text-6xl">
					<span style="--value:59; --digits: 2;" aria-live="polite" aria-label="59">59</span>
				</span>
				`
			},
			{
				name: "Countdown - Clock",
				icon_html: `<i aria-hidden="true" class="fa fa-clock-o"></i>`,
				template_html: `
				<span class="countdown font-mono text-2xl">
					<span style="--value:10;" aria-live="polite" aria-label="10">10</span>
					h
					<span style="--value:24;" aria-live="polite" aria-label="24">24</span>
					m
					<span style="--value:59;" aria-live="polite" aria-label="59">59</span>
					s
				</span>
				`
			},
			{
				name: "Countdown - Clock Colons",
				icon_html: `<i aria-hidden="true" class="fa fa-ellipsis-h"></i>`,
				template_html: `
				<span class="countdown font-mono text-2xl">
					<span style="--value:10;" aria-live="polite" aria-label="10">10</span>
					:
					<span style="--value:24; --digits: 2;" aria-live="polite" aria-label="24">24</span>
					:
					<span style="--value:59; --digits: 2;" aria-live="polite" aria-label="59">59</span>
				</span>
				`
			},
			{
				name: "Countdown - Labels Inline",
				icon_html: `<i aria-hidden="true" class="fa fa-list-ul"></i>`,
				template_html: `
				<div class="flex gap-5">
					<div>
						<span class="countdown font-mono text-4xl">
							<span style="--value:15;" aria-live="polite" aria-label="15">15</span>
						</span>
						days
					</div>
					<div>
						<span class="countdown font-mono text-4xl">
							<span style="--value:10;" aria-live="polite" aria-label="10">10</span>
						</span>
						hours
					</div>
					<div>
						<span class="countdown font-mono text-4xl">
							<span style="--value:24;" aria-live="polite" aria-label="24">24</span>
						</span>
						min
					</div>
					<div>
						<span class="countdown font-mono text-4xl">
							<span style="--value:59;" aria-live="polite" aria-label="59">59</span>
						</span>
						sec
					</div>
				</div>
				`
			},
			{
				name: "Countdown - Labels Under",
				icon_html: `<i aria-hidden="true" class="fa fa-th-large"></i>`,
				template_html: `
				<div class="grid auto-cols-max grid-flow-col gap-5 text-center">
					<div class="flex flex-col">
						<span class="countdown font-mono text-5xl">
							<span style="--value:15;" aria-live="polite" aria-label="15">15</span>
						</span>
						days
					</div>
					<div class="flex flex-col">
						<span class="countdown font-mono text-5xl">
							<span style="--value:10;" aria-live="polite" aria-label="10">10</span>
						</span>
						hours
					</div>
					<div class="flex flex-col">
						<span class="countdown font-mono text-5xl">
							<span style="--value:24;" aria-live="polite" aria-label="24">24</span>
						</span>
						min
					</div>
					<div class="flex flex-col">
						<span class="countdown font-mono text-5xl">
							<span style="--value:59;" aria-live="polite" aria-label="59">59</span>
						</span>
						sec
					</div>
				</div>
				`
			},
			{
				name: "Countdown - Boxes",
				icon_html: `<i aria-hidden="true" class="fa fa-square"></i>`,
				template_html: `
				<div class="grid auto-cols-max grid-flow-col gap-5 text-center">
					<div class="bg-neutral rounded-box text-neutral-content flex flex-col p-2">
						<span class="countdown font-mono text-5xl">
							<span style="--value:15;" aria-live="polite" aria-label="15">15</span>
						</span>
						days
					</div>
					<div class="bg-neutral rounded-box text-neutral-content flex flex-col p-2">
						<span class="countdown font-mono text-5xl">
							<span style="--value:10;" aria-live="polite" aria-label="10">10</span>
						</span>
						hours
					</div>
					<div class="bg-neutral rounded-box text-neutral-content flex flex-col p-2">
						<span class="countdown font-mono text-5xl">
							<span style="--value:24;" aria-live="polite" aria-label="24">24</span>
						</span>
						min
					</div>
					<div class="bg-neutral rounded-box text-neutral-content flex flex-col p-2">
						<span class="countdown font-mono text-5xl">
							<span style="--value:59;" aria-live="polite" aria-label="59">59</span>
						</span>
						sec
					</div>
				</div>
				`
			}
		]
	},

	"Stat": {
		description: "Stat examples from DaisyUI.",
		blocks: [
			{
				name: "Stat",
				icon_html: `<i aria-hidden="true" class="fa fa-line-chart"></i>`,
				template_html: `
				<div class="stats shadow">
					<div class="stat">
						<div editable="rich" class="stat-title">Total Page Views</div>
						<div editable="rich" class="stat-value">89,400</div>
						<div editable="rich" class="stat-desc">21% more than last month</div>
					</div>
				</div>
				`
			},
			{
				name: "Stat With Icons",
				icon_html: `<i aria-hidden="true" class="fa fa-heart"></i>`,
				template_html: `
				<div class="stats shadow">
					<div class="stat">
						<div class="stat-figure text-primary">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
							</svg>
						</div>
						<div editable="rich" class="stat-title">Total Likes</div>
						<div editable="rich" class="stat-value text-primary">25.6K</div>
						<div editable="rich" class="stat-desc">21% more than last month</div>
					</div>

					<div class="stat">
						<div class="stat-figure text-secondary">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
							</svg>
						</div>
						<div editable="rich" class="stat-title">Page Views</div>
						<div editable="rich" class="stat-value text-secondary">2.6M</div>
						<div editable="rich" class="stat-desc">21% more than last month</div>
					</div>

					<div class="stat">
						<div class="stat-figure text-secondary">
							<div class="avatar avatar-online">
								<div class="w-16 rounded-full">
									<img src="https://img.daisyui.com/images/profile/demo/anakeen@192.webp" />
								</div>
							</div>
						</div>
						<div editable="rich" class="stat-value">86%</div>
						<div editable="rich" class="stat-title">Tasks done</div>
						<div editable="rich" class="stat-desc text-secondary">31 tasks remaining</div>
					</div>
				</div>
				`
			},
			{
				name: "Stat Group",
				icon_html: `<i aria-hidden="true" class="fa fa-bar-chart"></i>`,
				template_html: `
				<div class="stats shadow">
					<div class="stat">
						<div class="stat-figure text-secondary">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
							</svg>
						</div>
						<div editable="rich" class="stat-title">Downloads</div>
						<div editable="rich" class="stat-value">31K</div>
						<div editable="rich" class="stat-desc">Jan 1st - Feb 1st</div>
					</div>

					<div class="stat">
						<div class="stat-figure text-secondary">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
							</svg>
						</div>
						<div editable="rich" class="stat-title">New Users</div>
						<div editable="rich" class="stat-value">4,200</div>
						<div editable="rich" class="stat-desc">↗︎ 400 (22%)</div>
					</div>

					<div class="stat">
						<div class="stat-figure text-secondary">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
							</svg>
						</div>
						<div editable="rich" class="stat-title">New Registers</div>
						<div editable="rich" class="stat-value">1,200</div>
						<div editable="rich" class="stat-desc">↘︎ 90 (14%)</div>
					</div>
				</div>
				`
			},
			{
				name: "Centered Items",
				icon_html: `<i aria-hidden="true" class="fa fa-align-center"></i>`,
				template_html: `
				<div class="stats shadow">
					<div class="stat place-items-center">
						<div editable="rich" class="stat-title">Downloads</div>
						<div editable="rich" class="stat-value">31K</div>
						<div editable="rich" class="stat-desc">From January 1st to February 1st</div>
					</div>

					<div class="stat place-items-center">
						<div editable="rich" class="stat-title">Users</div>
						<div editable="rich" class="stat-value text-secondary">4,200</div>
						<div editable="rich" class="stat-desc text-secondary">↗︎ 40 (2%)</div>
					</div>

					<div class="stat place-items-center">
						<div editable="rich" class="stat-title">New Registers</div>
						<div editable="rich" class="stat-value">1,200</div>
						<div editable="rich" class="stat-desc">↘︎ 90 (14%)</div>
					</div>
				</div>
				`
			},
			{
				name: "Vertical",
				icon_html: `<i aria-hidden="true" class="fa fa-align-justify"></i>`,
				template_html: `
				<div class="stats stats-vertical shadow">
					<div class="stat">
						<div editable="rich" class="stat-title">Downloads</div>
						<div editable="rich" class="stat-value">31K</div>
						<div editable="rich" class="stat-desc">Jan 1st - Feb 1st</div>
					</div>

					<div class="stat">
						<div editable="rich" class="stat-title">New Users</div>
						<div editable="rich" class="stat-value">4,200</div>
						<div editable="rich" class="stat-desc">↗︎ 400 (22%)</div>
					</div>

					<div class="stat">
						<div editable="rich" class="stat-title">New Registers</div>
						<div editable="rich" class="stat-value">1,200</div>
						<div editable="rich" class="stat-desc">↘︎ 90 (14%)</div>
					</div>
				</div>
				`
			},
			{
				name: "Responsive",
				icon_html: `<i aria-hidden="true" class="fa fa-arrows-h"></i>`,
				template_html: `
				<div class="stats stats-vertical lg:stats-horizontal shadow">
					<div class="stat">
						<div editable="rich" class="stat-title">Downloads</div>
						<div editable="rich" class="stat-value">31K</div>
						<div editable="rich" class="stat-desc">Jan 1st - Feb 1st</div>
					</div>

					<div class="stat">
						<div editable="rich" class="stat-title">New Users</div>
						<div editable="rich" class="stat-value">4,200</div>
						<div editable="rich" class="stat-desc">↗︎ 400 (22%)</div>
					</div>

					<div class="stat">
						<div editable="rich" class="stat-title">New Registers</div>
						<div editable="rich" class="stat-value">1,200</div>
						<div editable="rich" class="stat-desc">↘︎ 90 (14%)</div>
					</div>
				</div>
				`
			},
			{
				name: " Custom Colors",
				icon_html: `<i aria-hidden="true" class="fa fa-paint-brush"></i>`,
				template_html: `
				<div class="stats bg-base-100 border-base-300 border">
					<div class="stat">
						<div editable="rich" class="stat-title">Account balance</div>
						<div editable="rich" class="stat-value">$89,400</div>
						<div class="stat-actions">
							<button class="btn btn-xs btn-success">Add funds</button>
						</div>
					</div>

					<div class="stat">
						<div editable="rich" class="stat-title">Current balance</div>
						<div editable="rich" class="stat-value">$89,400</div>
						<div class="stat-actions">
							<button class="btn btn-xs">Withdrawal</button>
							<button class="btn btn-xs">Deposit</button>
						</div>
					</div>
				</div>
				`
			}
		]
	},


	"Image and Text": {
		description: "Content pairings that combine imagery with textual narratives.",
		blocks: [
			{
				name: "Image and text (center)",
				icon_html: `<svg xmlns="http://www.w3.org/2000/svg" width="43" height="29" viewBox="0 0 43 29" fill="none"> <path d="M40.8161 25.0059H1.54023C0.689584 25.0059 0 25.6954 0 26.5461C0 27.3967 0.689584 28.0863 1.54023 28.0863H40.8161C41.6667 28.0863 42.3563 27.3967 42.3563 26.5461C42.3563 25.6954 41.6667 25.0059 40.8161 25.0059Z" fill="#E0E7EC"></path> <path d="M38.9655 20H3.54023C2.68958 20 2 20.6896 2 21.5402C2 22.3909 2.68958 23.0805 3.54023 23.0805H38.9655C39.8161 23.0805 40.5057 22.3909 40.5057 21.5402C40.5057 20.6896 39.8161 20 38.9655 20Z" fill="#94A2AB"></path> <g clip-path="url(#clip0_55_10)"> <path d="M18.75 16.5H23.25C27 16.5 28.5 15 28.5 11.25V6.75C28.5 3 27 1.5 23.25 1.5H18.75C15 1.5 13.5 3 13.5 6.75V11.25C13.5 15 15 16.5 18.75 16.5Z" stroke="#94A2AB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M14.0025 14.2125L17.7 11.73C18.2925 11.3325 19.1475 11.3775 19.68 11.835L19.9275 12.0525C20.5125 12.555 21.4575 12.555 22.0425 12.0525L25.1625 9.375C25.7475 8.8725 26.6925 8.8725 27.2775 9.375L28.5 10.425M18.75 7.5C19.1478 7.5 19.5294 7.34196 19.8107 7.06066C20.092 6.77936 20.25 6.39782 20.25 6C20.25 5.60218 20.092 5.22064 19.8107 4.93934C19.5294 4.65804 19.1478 4.5 18.75 4.5C18.3522 4.5 17.9706 4.65804 17.6893 4.93934C17.408 5.22064 17.25 5.60218 17.25 6C17.25 6.39782 17.408 6.77936 17.6893 7.06066C17.9706 7.34196 18.3522 7.5 18.75 7.5Z" stroke="#94A2AB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g> <defs> <clipPath id="clip0_55_10"> <rect width="18" height="18" fill="white" transform="translate(12)"></rect> </clipPath> </defs> </svg>`,
				template_html: `
				<div class="card max-w-128 text-center">
					<div class="card-body flex gap-6" editable="rich">
						<img class="h-24 w-24 rounded mx-auto" src="https://cdn.livecanvas.com/media/thumbnails/img1.jpg" alt="Thumbnail" loading="lazy">
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc et metus id ligula malesuada placerat sit amet quis enim. Aliquam erat volutpat.</p>
					</div>
				</div>
				`
			},
			{
				name: "Horizontal Card",
				icon_html: `<svg fill="none" viewBox="0 0 65 14" width="65" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M23.5402 5.00586C22.6896 5.00586 22 5.69544 22 6.54609C22 7.39673 22.6896 8.08632 23.5402 8.08632H62.8161C63.6667 8.08632 64.3563 7.39673 64.3563 6.54609C64.3563 5.69544 63.6667 5.00586 62.8161 5.00586H23.5402ZM23.5402 10.0116C22.6896 10.0116 22 10.7012 22 11.5518C22 12.4025 22.6896 13.0921 23.5402 13.0921H51.2644C52.115 13.0921 52.8046 12.4025 52.8046 11.5518C52.8046 10.7012 52.115 10.0116 51.2644 10.0116H23.5402Z" fill="#E0E7EC" fill-rule="evenodd"></path><path clip-rule="evenodd" d="M23.5402 5.00586C22.6896 5.00586 22 5.69544 22 6.54609C22 7.39673 22.6896 8.08632 23.5402 8.08632H62.8161C63.6667 8.08632 64.3563 7.39673 64.3563 6.54609C64.3563 5.69544 63.6667 5.00586 62.8161 5.00586H23.5402ZM23.5402 10.0116C22.6896 10.0116 22 10.7012 22 11.5518C22 12.4025 22.6896 13.0921 23.5402 13.0921H51.2644C52.115 13.0921 52.8046 12.4025 52.8046 11.5518C52.8046 10.7012 52.115 10.0116 51.2644 10.0116H23.5402Z" fill="#E0E7EC" fill-rule="evenodd"></path><rect fill="#E0E7EC" height="3.08046" rx="1.54023" width="38.5057" x="22"></rect><path clip-rule="evenodd" d="M14.1826 3.19238C15.0567 3.19238 15.7652 2.47774 15.7652 1.59619C15.7652 0.714639 15.0567 0 14.1826 0C13.3086 0 12.6001 0.714639 12.6001 1.59619C12.6001 2.47774 13.3086 3.19238 14.1826 3.19238ZM9.80245 12.8581H1.27357C0.488838 12.8581 0.00987709 11.9955 0.424737 11.3294L5.92771 2.4938C6.3129 1.87534 7.20823 1.86267 7.60917 2.47105C8.62739 4.01607 10.38 6.68102 11.9065 9.03033L13.4112 6.60027C13.7876 5.99228 14.662 5.96445 15.0764 6.54728L18.4516 11.2947C18.9197 11.9532 18.4562 12.8662 17.6484 12.8741L14.3755 12.9063L9.80245 12.8581Z" fill="#9DAEB9" fill-rule="evenodd"></path></svg>`,
				template_html: `
				<div class="card max-w-128">
					<div class="card-body flex flex-row gap-6" editable="rich">
						<img class="h-24 w-24 rounded" src="https://cdn.livecanvas.com/media/thumbnails/img1.jpg" alt="Thumbnail" loading="lazy">
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc et metus id ligula malesuada placerat sit amet quis enim. Aliquam erat volutpat.</p>
					</div>
				</div>
				`
			},
			{
				name: "Horizontal Card, Right Align",
				icon_html: `<svg fill="none" viewBox="0 0 63 14" width="63" xmlns="http://www.w3.org/2000/svg"><rect fill="#E0E7EC" height="3.08046" rx="1.54023" transform="rotate(-180 42.3564 8.07471)" width="42.3563" x="42.3564" y="8.07471"></rect><rect fill="#E0E7EC" height="3.08046" rx="1.54023" transform="rotate(-180 42.3564 13.0806)" width="30.8046" x="42.3564" y="13.0806"></rect><rect fill="#E0E7EC" height="3.08046" rx="1.54023" transform="rotate(-180 42.3564 3.08057)" width="38.5057" x="42.3564" y="3.08057"></rect><path clip-rule="evenodd" d="M58.1826 3.19238C59.0567 3.19238 59.7652 2.47774 59.7652 1.59619C59.7652 0.714639 59.0567 0 58.1826 0C57.3086 0 56.6001 0.714639 56.6001 1.59619C56.6001 2.47774 57.3086 3.19238 58.1826 3.19238ZM53.8024 12.8581H45.2736C44.4888 12.8581 44.0099 11.9955 44.4247 11.3294L49.9277 2.4938C50.3129 1.87534 51.2082 1.86267 51.6092 2.47105C52.6274 4.01607 54.38 6.68102 55.9065 9.03033L57.4112 6.60027C57.7876 5.99228 58.662 5.96445 59.0764 6.54728L62.4516 11.2947C62.9197 11.9532 62.4562 12.8662 61.6484 12.8741L58.3755 12.9063L53.8024 12.8581Z" fill="#9DAEB9" fill-rule="evenodd"></path></svg>`,
				template_html: `
				<div class="card max-w-128">
					<div class="card-body flex flex-row gap-6" editable="rich">
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc et metus id ligula malesuada placerat sit amet quis enim. Aliquam erat volutpat.</p>
						<img class="h-24 w-24 rounded" src="https://cdn.livecanvas.com/media/thumbnails/img1.jpg" alt="Thumbnail" loading="lazy">
					</div>
				</div>
				`
			},
			{
				name: "Floating Image",
				icon_html: `<i class="fa fa-align-left" aria-hidden="true"></i>`,
				template_html: `
				<div class="card max-w-128">
					<div class="card-body inline-block" editable="rich">
						<img class="h-24 w-24 rounded float-start mb-2 mr-4" src="https://cdn.livecanvas.com/media/thumbnails/img1.jpg" alt="Thumbnail" loading="lazy">
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc et metus id ligula malesuada placerat sit amet quis enim. Aliquam erat volutpat. In pellentesque scelerisque auctor. Ut porta lacus eget nisi fermentum lobortis. Vestibulum facilisis tempor ipsum, ut rhoncus magna ultricies laoreet. Proin vehicula erat eget libero accumsan iaculis.</p>
					</div>
				</div>
				`
			},
			{
				name: "Floating Right",
				icon_html: `<i class="fa fa-align-right" aria-hidden="true"></i>`,
				template_html: `
				<div class="card max-w-128">
					<div class="card-body inline-block" editable="rich">
						<img class="h-24 w-24 rounded float-end mb-2 ms-4" src="https://cdn.livecanvas.com/media/thumbnails/img1.jpg" alt="Thumbnail" loading="lazy">

						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc et metus id ligula malesuada placerat sit amet quis enim. Aliquam erat volutpat. In pellentesque scelerisque auctor. Ut porta lacus eget nisi fermentum lobortis. Vestibulum facilisis tempor ipsum, ut rhoncus magna ultricies laoreet. Proin vehicula erat eget libero accumsan iaculis.</p>

					</div>
				</div>
				`
			},
			{
				name: "Longer Text",
				icon_html: `<i class="fa fa-file-text" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-base-100 shadow-xl">
					<div class="card-body grid gap-6 lg:grid-cols-2" editable="rich">
						<img class="w-full rounded-2xl object-cover" src="https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?auto=format&fit=crop&w=1200&q=80" alt="Forest" loading="lazy"/>
						<div class="space-y-4">
							<h2 class="text-3xl font-bold">The quick brown fox jumps over the lazy dog</h2>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse a lacus est. Etiam diam metus, lobortis non augue at, placerat viverra risus. Cras ornare faucibus laoreet. Aenean vel nisi in ipsum congue fermentum et ut arcu. Proin leo diam, vulputate eu tellus ac, mattis cursus nunc. In aliquet erat ac eros congue maximus. Fusce cursus leo at elit tincidunt, consequat ultrices ante pretium. Vivamus ut dapibus nisl, nec condimentum purus.</p> <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse a lacus est. Etiam diam metus, lobortis non augue at, placerat viverra risus. Cras ornare faucibus laoreet. Aenean vel nisi in ipsum congue fermentum et ut arcu. Proin leo diam, vulputate eu tellus ac, mattis cursus nunc. In aliquet erat ac eros congue maximus. Fusce cursus leo at elit tincidunt, consequat ultrices ante pretium. Vivamus ut dapibus nisl, nec condimentum purus.</p> <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse a lacus est. Etiam diam metus, lobortis non augue at, placerat viverra risus. Cras ornare faucibus laoreet. Aenean vel nisi in ipsum congue fermentum et ut arcu. Proin leo diam, vulputate eu tellus ac, mattis cursus nunc. In aliquet erat ac eros congue maximus. Fusce cursus leo at elit tincidunt, consequat ultrices ante pretium. Vivamus ut dapibus nisl, nec condimentum purus.</p>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Longer Text Right Aligned",
				icon_html: `<i class="fa fa-align-right" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-base-100 shadow-xl">
					<div class="card-body grid gap-6 lg:grid-cols-2" editable="rich">
						<div class="order-2 space-y-4 lg:order-1">
							<h2 class="text-3xl font-bold">Right aligned pairing</h2>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse a lacus est. Etiam diam metus, lobortis non augue at, placerat viverra risus. Cras ornare faucibus laoreet. Aenean vel nisi in ipsum congue fermentum et ut arcu. Proin leo diam, vulputate eu tellus ac, mattis cursus nunc. In aliquet erat ac eros congue maximus. Fusce cursus leo at elit tincidunt, consequat ultrices ante pretium. Vivamus ut dapibus nisl, nec condimentum purus.</p> <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse a lacus est. Etiam diam metus, lobortis non augue at, placerat viverra risus. Cras ornare faucibus laoreet. Aenean vel nisi in ipsum congue fermentum et ut arcu. Proin leo diam, vulputate eu tellus ac, mattis cursus nunc. In aliquet erat ac eros congue maximus. Fusce cursus leo at elit tincidunt, consequat ultrices ante pretium. Vivamus ut dapibus nisl, nec condimentum purus.</p> <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse a lacus est. Etiam diam metus, lobortis non augue at, placerat viverra risus. Cras ornare faucibus laoreet. Aenean vel nisi in ipsum congue fermentum et ut arcu. Proin leo diam, vulputate eu tellus ac, mattis cursus nunc. In aliquet erat ac eros congue maximus. Fusce cursus leo at elit tincidunt, consequat ultrices ante pretium. Vivamus ut dapibus nisl, nec condimentum purus.</p>
						</div>
						<img class="order-1 w-full rounded-2xl object-cover lg:order-2" src="https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?auto=format&fit=crop&w=1200&q=80" alt="River" loading="lazy"/>
					</div>
				</div>
				`
			},
			{
				name: "Circle Image on the Left",
				icon_html: `<i class="fa fa-user-circle" aria-hidden="true"></i>`,
				template_html: `
				<div class="card-body flex flex-row gap-4">
					<div class="avatar">
						<div class="w-14 h-14 rounded-full">
							<img src="https://images.unsplash.com/photo-1519066629447-267fffa62d4b?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=768" alt="Photo by Jeremy Avery" width="1080" height="768" srcset="https://images.unsplash.com/photo-1519066629447-267fffa62d4b?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1519066629447-267fffa62d4b??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1519066629447-267fffa62d4b??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1519066629447-267fffa62d4b??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1519066629447-267fffa62d4b??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px">
						</div>
					</div>
					<div editable="rich">
						<h5 class="text-xl font-semibold">Andrew J. Hoover</h5>
						<p class="text-sm">Art Director</p>
					</div>
				</div>
				`
			},
			{
				name: "Circle Image on the Right",
				icon_html: `<i class="fa fa-user-circle-o" aria-hidden="true"></i>`,
				template_html: `
				<div class="card">
					<div class="card-body flex flex-row gap-4">
						<div editable="rich">
							<h5 class="text-xl font-semibold">Andrew J. Hoover</h5>
							<p class="text-sm">Art Director</p>
						</div>
						<div class="avatar">
							<div class="w-14 h-14 rounded-full">
								<img src="https://images.unsplash.com/photo-1519066629447-267fffa62d4b?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=768" alt="Photo by Jeremy Avery" width="1080" height="768" srcset="https://images.unsplash.com/photo-1519066629447-267fffa62d4b?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1519066629447-267fffa62d4b??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1519066629447-267fffa62d4b??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1519066629447-267fffa62d4b??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1519066629447-267fffa62d4b??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8bGlvbnN8ZW58MHwwfHx8MTc2NjAxNDU0Nnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px">
							</div>
						</div>
					</div>
				</div>
				`
			}
		]
	},
	 "Icon and Text": {
		description: "Icon-led snippets for highlighting features or stats.",
		blocks: [
			{
				name: "Vertical Stack",
				icon_html: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="24" viewBox="0 0 64 24" fill="none"><rect x="4" y="22" width="56" height="2" rx="1" fill="#E0E7EC"></rect><circle cx="32" cy="7" r="5" fill="#94A2AB"></circle><circle cx="32" cy="7" r="3" fill="#C7D3DC"></circle><rect x="18" y="13" width="28" height="3" rx="1.5" fill="#94A2AB"></rect><rect x="20" y="17" width="24" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="22" y="21" width="20" height="2" rx="1" fill="#E0E7EC" opacity="0.7"></rect></svg>`,
				template_html: `<div class="card bg-base-100 p-6"> <div class="text-primary mb-3"> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="w-8 h-8" viewBox="0 0 24 24" style="" lc-helper="svg-icon" fill="currentColor"> <path d="M12 6C9.33 6 7.67 7.33 7 10C8 8.67 9.17 8.17 10.5 8.5C11.26 8.69 11.81 9.24 12.41 9.85C13.39 10.85 14.5 12 17 12C19.67 12 21.33 10.67 22 8C21 9.33 19.83 9.83 18.5 9.5C17.74 9.31 17.2 8.76 16.59 8.15C15.61 7.15 14.5 6 12 6M7 12C4.33 12 2.67 13.33 2 16C3 14.67 4.17 14.17 5.5 14.5C6.26 14.69 6.8 15.24 7.41 15.85C8.39 16.85 9.5 18 12 18C14.67 18 16.33 16.67 17 14C16 15.33 14.83 15.83 13.5 15.5C12.74 15.31 12.2 14.76 11.59 14.15C10.61 13.15 9.5 12 7 12Z"></path> </svg> </div> <div editable="rich"> <h4 class="card-title text-lg font-semibold">Tailwind Integration</h4> </div> </div>`
			},
			{
				name: "With Two Labels",
				icon_html: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="24" viewBox="0 0 64 24" fill="none"><rect x="4" y="22" width="56" height="2" rx="1" fill="#E0E7EC"></rect><circle cx="32" cy="7" r="5" fill="#94A2AB"></circle><circle cx="32" cy="7" r="3" fill="#C7D3DC"></circle><rect x="18" y="13" width="28" height="3" rx="1.5" fill="#94A2AB"></rect><rect x="14" y="18" width="16" height="3" rx="1.5" fill="#94A2AB"></rect><rect x="34" y="18" width="16" height="3" rx="1.5" fill="#C7D3DC"></rect></svg>`,
				template_html: `<div class="card bg-base-100 text-center"> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="w-24 h-24 mx-auto" viewBox="0 0 24 24" style="" lc-helper="svg-icon" fill="currentColor"> <path d="M12 6C9.33 6 7.67 7.33 7 10C8 8.67 9.17 8.17 10.5 8.5C11.26 8.69 11.81 9.24 12.41 9.85C13.39 10.85 14.5 12 17 12C19.67 12 21.33 10.67 22 8C21 9.33 19.83 9.83 18.5 9.5C17.74 9.31 17.2 8.76 16.59 8.15C15.61 7.15 14.5 6 12 6M7 12C4.33 12 2.67 13.33 2 16C3 14.67 4.17 14.17 5.5 14.5C6.26 14.69 6.8 15.24 7.41 15.85C8.39 16.85 9.5 18 12 18C14.67 18 16.33 16.67 17 14C16 15.33 14.83 15.83 13.5 15.5C12.74 15.31 12.2 14.76 11.59 14.15C10.61 13.15 9.5 12 7 12Z"></path> </svg> <div editable="rich"> <p class="font-bold text-3xl">+150</p> <p class="text-base-content/60">Icons</p> </div> </div>`
			},
			{
				name: "Horizontal Stack",
				icon_html: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="24" viewBox="0 0 64 24" fill="none"><rect x="4" y="22" width="56" height="2" rx="1" fill="#E0E7EC"></rect><circle cx="15" cy="12" r="5" fill="#94A2AB"></circle><circle cx="15" cy="12" r="3" fill="#C7D3DC"></circle><rect x="30" y="6" width="30" height="3" rx="1.5" fill="#94A2AB"></rect><rect x="30" y="10" width="28" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="30" y="14" width="26" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="30" y="18" width="22" height="2" rx="1" fill="#E0E7EC" opacity="0.8"></rect></svg>`,
				template_html: `<div class="card flex flex-row items-center gap-3 p-4"> <svg class="w-8 h-8 flex-shrink-0" fill="currentColor" lc-helper="svg-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"> <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" fill-rule="evenodd"></path> <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.236.236 0 0 1 .02-.022z" fill-rule="evenodd"></path> </svg> <div class="flex-1" editable="rich"> <p>Your task has been completed successfully and all changes have been saved.</p> </div> </div>`
			},
			{
				name: "Right Aligned",
				icon_html: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="24" viewBox="0 0 64 24" fill="none"><rect x="4" y="22" width="56" height="2" rx="1" fill="#E0E7EC"></rect><circle cx="49" cy="11" r="5" fill="#94A2AB"></circle><circle cx="49" cy="11" r="3" fill="#C7D3DC"></circle><rect x="12" y="8" width="26" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="14" y="12" width="24" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="20" y="17" width="18" height="3" rx="1.5" fill="#94A2AB"></rect></svg>`,
				template_html: `<div class="card flex flex-row items-center gap-3 p-4 justify-end"> <div class="flex-1 text-right" editable="rich"> <p>Your task has been completed successfully and all changes have been saved.</p> </div> <svg class="w-8 h-8 flex-shrink-0" fill="currentColor" lc-helper="svg-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"> <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" fill-rule="evenodd"></path> <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.236.236 0 0 1 .02-.022z" fill-rule="evenodd"></path> </svg> </div>`
			},
			{
				name: "With Paragraph",
				icon_html: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="24" viewBox="0 0 64 24" fill="none"><rect x="4" y="22" width="56" height="2" rx="1" fill="#E0E7EC"></rect><circle cx="14" cy="12" r="5" fill="#94A2AB"></circle><circle cx="14" cy="12" r="3" fill="#C7D3DC"></circle><rect x="26" y="6" width="32" height="3" rx="1.5" fill="#94A2AB"></rect><rect x="26" y="10" width="32" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="26" y="14" width="30" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="26" y="18" width="28" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="26" y="21" width="24" height="2" rx="1" fill="#E0E7EC" opacity="0.7"></rect></svg>`,
				template_html: `<div class="flex px-4"> <svg class="w-6 h-6 text-primary shrink-0" fill="currentColor" height="1em" lc-helper="svg-icon" viewBox="0 0 16 16" width="1em" xmlns="http://www.w3.org/2000/svg"> <path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1V2z" fill-rule="evenodd"></path> </svg> <div class="lc-block ps-4"> <div editable="rich"> <h3 class="text-lg font-semibold">Analytics Dashboard</h3> <p class="text-base-content/70">Track your business performance with real-time analytics and comprehensive reporting tools that help you make data-driven decisions.</p> </div> </div> </div>`
			},
			{
				name: "Right Aligned",
				icon_html: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="24" viewBox="0 0 64 24" fill="none"><rect x="4" y="22" width="56" height="2" rx="1" fill="#E0E7EC"></rect><circle cx="51" cy="12" r="5" fill="#94A2AB"></circle><circle cx="51" cy="12" r="3" fill="#C7D3DC"></circle><rect x="12" y="6" width="30" height="3" rx="1.5" fill="#94A2AB"></rect><rect x="14" y="10" width="28" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="16" y="14" width="26" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="18" y="18" width="24" height="3" rx="1.5" fill="#E0E7EC"></rect><rect x="20" y="21" width="20" height="2" rx="1" fill="#E0E7EC" opacity="0.7"></rect></svg>`,
				template_html: `<div class="flex px-4 justify-end text-right"> <div class="lc-block pe-4"> <div editable="rich"> <h3 class="text-lg font-semibold">Analytics Dashboard</h3> <p class="text-base-content/70">Track your business performance with real-time analytics and comprehensive reporting tools that help you make data-driven decisions.</p> </div> </div> <svg class="w-6 h-6 text-primary shrink-0" fill="currentColor" height="1em" lc-helper="svg-icon" viewBox="0 0 16 16" width="1em" xmlns="http://www.w3.org/2000/svg"> <path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1V2z" fill-rule="evenodd"></path> </svg> </div>`
			},
		],
	},
	"Cards": {
		description: "Card component patterns built with DaisyUI.",
		blocks: [
			{
				name: "Card – Body then Image",
				icon_html: `<i class="fa fa-square" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-base-100 w-96 mx-auto shadow-sm">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Card Title</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
					</div>
					<figure>
						<img src="https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp" alt="Shoes"/>
					</figure>
				</div>
				`
			},
			{
				name: "Card – Image Top Centered",
				icon_html: `<i class="fa fa-clone" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-base-100 w-96 mx-auto shadow-sm">
					<figure class="px-10 pt-10">
						<img class="rounded-xl" src="https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp" alt="Shoes"/>
					</figure>
					<div class="card-body items-center text-center">
						<h2 class="card-title" editable="inline">Card Title</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions">
							<button class="btn btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Image Full",
				icon_html: `<i class="fa fa-picture-o" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-base-100 image-full mx-auto w-96 shadow-sm">
					<figure>
						<img src="https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp" alt="Shoes"/>
					</figure>
					<div class="card-body">
						<h2 class="card-title" editable="inline">Card Title</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Simple Action",
				icon_html: `<i class="fa fa-credit-card" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-base-100 w-96 mx-auto shadow-sm">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Card title!</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Primary Variant",
				icon_html: `<i class="fa fa-paint-brush" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-primary text-primary-content w-96 mx-auto">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Card title!</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Side Image",
				icon_html: `<i class="fa fa-columns" aria-hidden="true"></i>`,
				template_html: `
				<div class="card card-side bg-base-100 shadow-sm ">
					<figure>
						<img src="https://img.daisyui.com/images/stock/photo-1635805737707-575885ab0820.webp" alt="Movie"/>
					</figure>
					<div class="card-body">
						<h2 class="card-title" editable="inline">New movie is released!</h2>
						<p editable="rich">Click the button to watch on Jetflix app.</p>
						<div class="card-actions justify-end">
							<button class="btn btn-primary">Watch</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Badges",
				icon_html: `<i class="fa fa-bookmark" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-base-100 w-96 shadow-sm mx-auto">
					<figure>
						<img src="https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp" alt="Shoes"/>
					</figure>
					<div class="card-body">
						<h2 class="card-title" editable="inline">
							Card Title
							<div class="badge badge-secondary">NEW</div>
						</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<div class="badge badge-outline">Fashion</div>
							<div class="badge badge-outline">Products</div>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Image Top",
				icon_html: `<i class="fa fa-picture-o" aria-hidden="true"></i>`,
				template_html: `
				<div class="card bg-base-100 w-96 shadow-sm mx-auto">
					<figure>
						<img src="https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp" alt="Shoes"/>
					</figure>
					<div class="card-body">
						<h2 class="card-title" editable="inline">Card Title</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Pricing",
				icon_html: `<i class="fa fa-money" aria-hidden="true"></i>`,
				template_html: `
				<div class="card w-96 bg-base-100 shadow-sm mx-auto">
					<div class="card-body">
						<span class="badge badge-xs badge-warning">Most Popular</span>
						<div class="flex justify-between">
							<h2 class="text-3xl font-bold" editable="inline">Premium</h2>
							<span class="text-xl" editable="inline">$29/mo</span>
						</div>
						<ul class="mt-6 flex flex-col gap-2 text-xs">
							<li>
								<svg xmlns="http://www.w3.org/2000/svg" class="size-4 me-2 inline-block text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
								<span editable="rich">High-resolution image generation</span>
							</li>
							<li>
								<svg xmlns="http://www.w3.org/2000/svg" class="size-4 me-2 inline-block text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
								<span editable="rich">Customizable style templates</span>
							</li>
							<li>
								<svg xmlns="http://www.w3.org/2000/svg" class="size-4 me-2 inline-block text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
								<span editable="rich">Batch processing capabilities</span>
							</li>
							<li>
								<svg xmlns="http://www.w3.org/2000/svg" class="size-4 me-2 inline-block text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
								<span editable="rich">AI-driven image enhancements</span>
							</li>
							<li class="opacity-50">
								<svg xmlns="http://www.w3.org/2000/svg" class="size-4 me-2 inline-block text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
								<span class="line-through" editable="rich">Seamless cloud integration</span>
							</li>
							<li class="opacity-50">
								<svg xmlns="http://www.w3.org/2000/svg" class="size-4 me-2 inline-block text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
								<span class="line-through" editable="rich">Real-time collaboration tools</span>
							</li>
						</ul>
						<div class="mt-6">
							<button class="btn btn-primary btn-block">Subscribe</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Extra Small",
				icon_html: `<i class="fa fa-compress" aria-hidden="true"></i>`,
				template_html: `
				<div class="card w-96 bg-base-100 card-xs shadow-sm">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Xsmall Card</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-xs btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Small",
				icon_html: `<i class="fa fa-minus-square" aria-hidden="true"></i>`,
				template_html: `
				<div class="card w-96 bg-base-100 card-sm shadow-sm">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Small Card</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-sm btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Medium",
				icon_html: `<i class="fa fa-square-o" aria-hidden="true"></i>`,
				template_html: `
				<div class="card w-96 bg-base-100 card-md shadow-sm">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Medium Card</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Large",
				icon_html: `<i class="fa fa-plus-square" aria-hidden="true"></i>`,
				template_html: `
				<div class="card w-96 bg-base-100 card-lg shadow-sm">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Large Card</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-lg btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Extra Large",
				icon_html: `<i class="fa fa-expand" aria-hidden="true"></i>`,
				template_html: `
				<div class="card w-96 bg-base-100 card-xl shadow-sm">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Xlarge Card</h2>
						<p class="mb-4" editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-xl btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Border",
				icon_html: `<i class="fa fa-square" aria-hidden="true"></i>`,
				template_html: `
				<div class="card card-border bg-base-100 w-96 mx-auto">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Card Title</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Dash",
				icon_html: `<i class="fa fa-ellipsis-h" aria-hidden="true"></i>`,
				template_html: `
				<div class="card card-dash bg-base-100 w-96 mx-auto">
					<div class="card-body">
						<h2 class="card-title" editable="inline">Card Title</h2>
						<p editable="rich">A card component has a figure, a body part, and inside body there are title and actions parts</p>
						<div class="card-actions justify-end">
							<button class="btn btn-primary">Buy Now</button>
						</div>
					</div>
				</div>
				`
			},
			{
				name: "Card – Responsive Side",
				icon_html: `<i class="fa fa-music" aria-hidden="true"></i>`,
				template_html: `
				<div class="card lg:card-side bg-base-100 shadow-sm ">
					<figure>
						<img src="https://img.daisyui.com/images/stock/photo-1494232410401-ad00d5433cfa.webp" alt="Album"/>
					</figure>
					<div class="card-body">
						<h2 class="card-title" editable="inline">New album is released!</h2>
						<p editable="rich">Click the button to listen on Spotiwhy app.</p>
						<div class="card-actions justify-end">
							<button class="btn btn-primary">Listen</button>
						</div>
					</div>
				</div>
				`
			}
		]
	},
	 
	"Galleries": {
		description: "Curated image grids for showcasing photography.",
		blocks: [
			{
				name: "Gallery with Two images",
				icon_html: `<i aria-hidden="true" class="fa fa-columns"></i>`,
				template_html: `
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1565021324587-5fd009870e68?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8ODJ8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjc0NA&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1565021324587-5fd009870e68?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8ODJ8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjc0NA&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1565021324587-5fd009870e68??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8ODJ8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjc0NA&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1565021324587-5fd009870e68??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8ODJ8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjc0NA&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1565021324587-5fd009870e68??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8ODJ8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjc0NA&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1565021324587-5fd009870e68??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8ODJ8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjc0NA&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Félix Besombes" loading="lazy">
						</div>
	
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1530533718754-001d2668365a?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTN8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjcwOQ&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1530533718754-001d2668365a?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTN8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjcwOQ&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1530533718754-001d2668365a??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTN8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjcwOQ&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1530533718754-001d2668365a??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTN8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjcwOQ&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1530533718754-001d2668365a??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTN8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjcwOQ&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1530533718754-001d2668365a??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTN8fGJsdWV8ZW58MHwwfHx8MTYzNTAwMjcwOQ&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Fabrizio Conti" loading="lazy">
						</div>
					</div>`
			},
					
			{
				name: "Three Images Grid",
				icon_html: `<i aria-hidden="true" class="fa fa-th-large"></i>`,
				template_html: `
					<div class="grid grid-cols-1 md:grid-cols-3 gap-2">
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1518640467707-6811f4a6ab73?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8dGVhbHxlbnwwfDJ8fHwxNzYwMDA0ODk1fDA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" alt="Photo by Bia W. A." loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1672508896690-b771a596cb82?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8M3x8dGVhbHxlbnwwfDJ8fHwxNzYwMDA0ODk1fDA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" alt="Photo by SIVASURYA SA" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1595503882016-b2fc58df42b1?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8NXx8dGVhbHxlbnwwfDJ8fHwxNzYwMDA0ODk1fDA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" alt="Photo by Alexander Jawfox" loading="lazy">
						</div>
					</div>`
			},
			{
				name: "Gallery with 4 images",
				icon_html: `<i aria-hidden="true" class="fa fa-th"></i>`,
				template_html: `
				<div class="grid grid-cols-2 md:grid-cols-4 gap-2">
					<div class="lc-block">
						<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1640258700146-cf24dc8d6b2f?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MTB8fHJlZHxlbnwwfDJ8fHwxNzYwMDA2NDg2fDA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" alt="Photo by Wilhelm Gunkel" loading="lazy" width="1080" height="1080">
					</div>
					<div class="lc-block">
						<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1639907087057-971905eeda58?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8OXx8cmVkfGVufDB8Mnx8fDE3NjAwMDY0ODZ8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" alt="Photo by Wilhelm Gunkel" loading="lazy" width="1080" height="1080">
					</div>
					<div class="lc-block">
						<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1568561300108-e0c35b5f7c1c?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MXx8cmVkfGVufDB8Mnx8fDE3NjAwMDY0ODZ8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" alt="Photo by Jason Dent" loading="lazy" width="1080" height="1080">
					</div>
					<div class="lc-block">
						<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1542324216541-c84c8ba6db04?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8NXx8cmVkfGVufDB8Mnx8fDE3NjAwMDY0ODZ8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" alt="Photo by Nikita Tikhomirov" loading="lazy" width="1080" height="1080">
					</div>
				</div>`
			},
			{
				name: "Four Images Grid",
				icon_html: `<i aria-hidden="true" class="fa fa-th-large"></i>`,
				template_html: `
					<div class="grid grid-cols-2 gap-2">
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" loading="lazy" alt="Photo by Michel Rocha" src="https://images.unsplash.com/photo-1543006889-52c98a83b4c1?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MjN8fGdyZWVufGVufDB8Mnx8fDE3NjAwMDY4MjV8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" width="1080" height="1080">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" loading="lazy" alt="Photo by Yale Cohen" src="https://images.unsplash.com/photo-1557550388-645cc4687130?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MTl8fGdyZWVufGVufDB8Mnx8fDE3NjAwMDY4MDl8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" width="1080" height="1080">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" loading="lazy" alt="Photo by Dominick Cheers" src="https://images.unsplash.com/photo-1628135112813-fdb1a054607b?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8OTl8fGdyZWVufGVufDB8Mnx8fDE3NjAwMDY4NzB8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" width="1080" height="1080">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1560711104-f1153716e5b3?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=avif&amp;ixid=M3wzNzg0fDB8MXxzZWFyY2h8MTI5fHxncmVlbnxlbnwwfDJ8fHwxNzYwMDA2OTU2fDA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080&amp;h=1080" alt="Photo by Mark Boss" width="1080" height="1080">
						</div>
					</div>`
			},
			{
				name: "Six images Grid",
				icon_html: `<i aria-hidden="true" class="fa fa-th-large"></i>`,
				template_html: `
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1602108987428-4768d7c7ecbe?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8M3x8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1602108987428-4768d7c7ecbe?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8M3x8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1602108987428-4768d7c7ecbe??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8M3x8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1602108987428-4768d7c7ecbe??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8M3x8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1602108987428-4768d7c7ecbe??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8M3x8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1602108987428-4768d7c7ecbe??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8M3x8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Bekky Bekks" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1521913626209-0fbf68f4c4b1?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8NXx8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1521913626209-0fbf68f4c4b1?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8NXx8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1521913626209-0fbf68f4c4b1??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8NXx8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1521913626209-0fbf68f4c4b1??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8NXx8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1521913626209-0fbf68f4c4b1??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8NXx8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1521913626209-0fbf68f4c4b1??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8NXx8eWVsbG93fGVufDB8MHx8fDE2MzUwMDY5MTE&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Jason Leung" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1516641051054-9df6a1aad654?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjR8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTI5&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1516641051054-9df6a1aad654?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjR8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTI5&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1516641051054-9df6a1aad654??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjR8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTI5&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1516641051054-9df6a1aad654??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjR8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTI5&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1516641051054-9df6a1aad654??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjR8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTI5&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1516641051054-9df6a1aad654??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjR8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTI5&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by JOSE LARRAZOLO" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1438183972690-6d4658e3290e?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTZ8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1438183972690-6d4658e3290e?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTZ8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1438183972690-6d4658e3290e??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTZ8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1438183972690-6d4658e3290e??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTZ8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1438183972690-6d4658e3290e??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTZ8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1438183972690-6d4658e3290e??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTZ8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Alexey Lin" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1534531173927-aeb928d54385?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTF8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1534531173927-aeb928d54385?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTF8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1534531173927-aeb928d54385??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTF8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1534531173927-aeb928d54385??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTF8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1534531173927-aeb928d54385??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTF8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1534531173927-aeb928d54385??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTF8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Markus Spiske" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1528105862282-e4333365c1d4?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1528105862282-e4333365c1d4?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1528105862282-e4333365c1d4??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1528105862282-e4333365c1d4??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1528105862282-e4333365c1d4??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1528105862282-e4333365c1d4??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fHllbGxvd3xlbnwwfDB8fHwxNjM1MDA2OTE4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Lubomirkin" loading="lazy">
						</div>
					</div>`
			},
			{
				name: "Eight images Grid",
				icon_html: `<i aria-hidden="true" class="fa fa-th-large"></i>`,
				template_html: `
					<div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1477936821694-ec4233a9a1a0?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8N3x8b3JhbmdlfGVufDB8MHx8fDE2MzUwMDE5MTg&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1477936821694-ec4233a9a1a0?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8N3x8b3JhbmdlfGVufDB8MHx8fDE2MzUwMDE5MTg&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1477936821694-ec4233a9a1a0??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8N3x8b3JhbmdlfGVufDB8MHx8fDE2MzUwMDE5MTg&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1477936821694-ec4233a9a1a0??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8N3x8b3JhbmdlfGVufDB8MHx8fDE2MzUwMDE5MTg&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1477936821694-ec4233a9a1a0??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8N3x8b3JhbmdlfGVufDB8MHx8fDE2MzUwMDE5MTg&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1477936821694-ec4233a9a1a0??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8N3x8b3JhbmdlfGVufDB8MHx8fDE2MzUwMDE5MTg&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Josh Rose" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1495366554757-8568e69d7f80?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTV8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1495366554757-8568e69d7f80?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTV8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1495366554757-8568e69d7f80??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTV8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1495366554757-8568e69d7f80??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTV8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1495366554757-8568e69d7f80??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTV8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1495366554757-8568e69d7f80??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTV8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Denise Bossarte" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1598048150218-53ab5609ef31?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1598048150218-53ab5609ef31?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1598048150218-53ab5609ef31??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1598048150218-53ab5609ef31??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1598048150218-53ab5609ef31??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1598048150218-53ab5609ef31??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTh8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Danilo Alvesd" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1617957718614-8c23f060c2d0?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTd8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1617957718614-8c23f060c2d0?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTd8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1617957718614-8c23f060c2d0??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTd8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1617957718614-8c23f060c2d0??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTd8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1617957718614-8c23f060c2d0??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTd8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1617957718614-8c23f060c2d0??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTd8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Sincerely Media" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1557682260-96773eb01377?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1557682260-96773eb01377?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1557682260-96773eb01377??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1557682260-96773eb01377??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1557682260-96773eb01377??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1557682260-96773eb01377??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MjB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Luke Chesser" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1502622796232-e88458466c33?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTJ8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1502622796232-e88458466c33?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTJ8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1502622796232-e88458466c33??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTJ8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1502622796232-e88458466c33??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTJ8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1502622796232-e88458466c33??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTJ8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1502622796232-e88458466c33??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MTJ8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTI1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Natalia Y" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1529089202281-2180f7a2289c?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTM4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1529089202281-2180f7a2289c?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTM4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1529089202281-2180f7a2289c??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTM4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1529089202281-2180f7a2289c??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTM4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1529089202281-2180f7a2289c??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTM4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1529089202281-2180f7a2289c??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzB8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTM4&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Maxime Lebrun" loading="lazy">
						</div>
						<div class="lc-block">
							<img class="w-full h-auto rounded-box shadow" src="https://images.unsplash.com/photo-1499088513455-78ed88b7a5b4?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzR8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTQ1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768" srcset="https://images.unsplash.com/photo-1499088513455-78ed88b7a5b4?crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzR8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTQ1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1080&amp;h=768 1080w, https://images.unsplash.com/photo-1499088513455-78ed88b7a5b4??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzR8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTQ1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=150 150w, https://images.unsplash.com/photo-1499088513455-78ed88b7a5b4??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzR8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTQ1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=300 300w, https://images.unsplash.com/photo-1499088513455-78ed88b7a5b4??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzR8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTQ1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=768 768w, https://images.unsplash.com/photo-1499088513455-78ed88b7a5b4??crop=entropy&amp;cs=tinysrgb&amp;fit=crop&amp;fm=jpg&amp;ixid=MnwzNzg0fDB8MXxzZWFyY2h8MzR8fG9yYW5nZXxlbnwwfDB8fHwxNjM1MDAxOTQ1&amp;ixlib=rb-1.2.1&amp;q=80&amp;w=1024 1024w" sizes="(max-width: 1080px) 100vw, 1080px" width="1080" height="768" alt="Photo by Tools For Motivation" loading="lazy">
						</div>
					</div>`
			},
		],
	},
	"Layouts": {
		description: "Responsive scaffolds for structuring sections and grids.",
		blocks: [
			{
				name: "Flex Layout 1",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="24" height="18" rx="1"/><rect x="24" y="4" width="24" height="18" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-2 gap-0 overflow-hidden">
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			</div>`
			},
			{
				name: "Flex Layout 2",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="16" height="18" rx="1"/><rect x="16" y="4" width="16" height="18" rx="1"/><rect x="32" y="4" width="16" height="18" rx="1"/></g></svg>`,
 				template_html: `<div class="grid grid-cols-1 lg:grid-cols-3 gap-0 overflow-hidden">
		<div class="lc-block"></div>
		<div class="lc-block"></div>
		<div class="lc-block"></div>
		</div>`
			},
			{
				name: "Flex Layout 3",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g stroke="currentColor" stroke-width="0.8" fill="currentColor" fill-opacity=".15"><rect x="0" y="4" width="24" height="18" rx="1"/><rect x="24" y="4" width="24" height="18" rx="1"/></g><g fill="currentColor" fill-opacity=".25" stroke="currentColor" stroke-width="0.6"><rect x="26" y="6" width="20" height="7" rx="1"/><rect x="26" y="13.5" width="20" height="7" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-2 gap-0 overflow-hidden">
			<div class="lc-block"></div>
			<div class="grid grid-cols-1 gap-0">
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			</div>
			</div>`
			},
			{
				name: "Flex Layout 4",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g stroke="currentColor" stroke-width="0.8" fill="currentColor" fill-opacity=".15"><rect x="0" y="4" width="24" height="18" rx="1"/><rect x="24" y="4" width="24" height="18" rx="1"/></g><g fill="currentColor" fill-opacity=".25" stroke="currentColor" stroke-width="0.6"><rect x="2" y="6" width="20" height="7" rx="1"/><rect x="2" y="13.5" width="20" height="7" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-2 gap-0 overflow-hidden">
			<div class="grid grid-cols-1 gap-0">
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			</div>
			<div class="lc-block"></div>
			</div>`
			},
			{
				name: "Flex Layout 5",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="24" height="8" rx="1"/><rect x="24" y="4" width="24" height="8" rx="1"/><rect x="0" y="14" width="48" height="8" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-2 gap-0 overflow-hidden">
			<div class="lc-block lg:col-span-1"></div>
			<div class="lc-block lg:col-span-1"></div>
			<div class="lc-block col-span-full lg:col-span-2"></div>
			</div>`
			},
			{
				name: "Flex Layout 6",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="48" height="8" rx="1"/><rect x="0" y="14" width="24" height="8" rx="1"/><rect x="24" y="14" width="24" height="8" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-2 gap-0 overflow-hidden">
			<div class="lc-block col-span-full lg:col-span-2"></div>
			<div class="lc-block lg:col-span-1"></div>
			<div class="lc-block lg:col-span-1"></div>
			</div>`
			},
			{
				name: "Flex Layout 7",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="24" height="8" rx="1"/><rect x="24" y="4" width="24" height="8" rx="1"/><rect x="0" y="14" width="24" height="8" rx="1"/><rect x="24" y="14" width="24" height="8" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-2 gap-0 overflow-hidden">
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			</div>`
			},
			{
				name: "Flex Layout 8",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="32" height="8" rx="1"/><rect x="32" y="4" width="16" height="8" rx="1"/><rect x="0" y="14" width="16" height="8" rx="1"/><rect x="16" y="14" width="32" height="8" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-hidden">
			<div class="lc-block lg:col-span-8"></div>
			<div class="lc-block lg:col-span-4"></div>
			<div class="lc-block lg:col-span-4"></div>
			<div class="lc-block lg:col-span-8"></div>
			</div>`
			},
			{
				name: "Flex Layout 9",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="16" height="8" rx="1"/><rect x="16" y="4" width="32" height="8" rx="1"/><rect x="0" y="14" width="32" height="8" rx="1"/><rect x="32" y="14" width="16" height="8" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-hidden">
			<div class="lc-block lg:col-span-4"></div>
			<div class="lc-block lg:col-span-8"></div>
			<div class="lc-block lg:col-span-8"></div>
			<div class="lc-block lg:col-span-4"></div>
			</div>`
			},
			{
				name: "Flex Layout 10",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="16" height="8" rx="1"/><rect x="16" y="4" width="16" height="8" rx="1"/><rect x="32" y="4" width="16" height="8" rx="1"/><rect x="0" y="14" width="16" height="8" rx="1"/><rect x="16" y="14" width="16" height="8" rx="1"/><rect x="32" y="14" width="16" height="8" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-3 gap-0 overflow-hidden">
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			<div class="lc-block"></div>
			</div>`
			},
			{
				name: "Flex Layout 11",
				icon_html: `<svg viewBox="0 0 48 26" width="24" height="13" xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="48" height="26" rx="3" fill="none" stroke="currentColor" opacity=".35"/><g fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="0.8"><rect x="0" y="4" width="16" height="8" rx="1"/><rect x="16" y="4" width="16" height="8" rx="1"/><rect x="32" y="4" width="16" height="8" rx="1"/><rect x="0" y="14" width="16" height="8" rx="1"/><rect x="16" y="14" width="32" height="8" rx="1"/></g></svg>`,
				template_html: `<div class="grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-hidden">
			<div class="lc-block lg:col-span-4"></div>
			<div class="lc-block lg:col-span-4"></div>
			<div class="lc-block lg:col-span-4"></div>
			<div class="lc-block lg:col-span-4"></div>
			<div class="lc-block lg:col-span-8"></div>
			</div>`
			}
		],
	},

	"Dynamic Post Loops": {
		description: "Loop-based layouts sourced from the sections library.",
		blocks: [
			{
				name: "Simple Posts Loop",
				icon_html: `<i aria-hidden="true" class="fa fa-newspaper-o"></i>`,
				template_html: ` 	<tangible class="live-refresh">

		<div class="grid lg:flex items-start gap-6">
			<loop class="grid gap-3 max-w-4xl mx-auto w-full" type="post" paged="4" orderby="date" order="desc">
				<div class="card card-side shadow-sm">
					<figure>
						<if field="image">
								<img class="w-24 h-24 object-cover" loading="lazy" src="{Field image_url size='thumbnail'}" srcset="{Field image_srcset}" sizes="{Field image_sizes}" alt="{Field image_alt}">

								<else>
									<img class="w-24 h-24 object-cover" loading="lazy" src="https://placehold.co/150" alt="Placeholder">
								</else>
							</if>
					</figure>
					<div class="card-body">
						<h2 class="card-title">
								<field name="title">
									</field>
						</h2>
						<p><field name="publish_date" date_format="F j, Y">
									</field></p>
						<div class="card-actions justify-end">
							<a class="btn btn-primary" href="{Field url}">Read</a> 
						</div>
					</div>
				</div>

				 
			</loop>
			<div class="sticky top-5 shrink-0">
				<!-- pagination -->
				<paginatebuttons></paginatebuttons>
			</div>
		</div>
	</tangible>`
			},

			{
				name: " Simple Two Column",
				icon_html: `<i aria-hidden="true" class="fa fa-columns"></i>`,
				template_html: `<tangible class="live-refresh">
					<div class="flex flex-col-reverse lg:flex-col gap-3">
						<loop class="grid gap-6 mb-5 sm:grid-cols-2" type="post" paged="4" orderby="date" order="desc">
							<div class="py-4 relative">
								<div class="h-full flex flex-col">
									<div class="aspect-[21/9] mb-3 overflow-hidden rounded">
										<if field="image">
											<!-- If the post has a featured image -->
											<img class="w-full h-full object-cover" src="{Field image_url size='large'}" alt="{Field image_alt}">
											<else>
												<!-- If no image, use placeholder -->
												<img class="w-full h-full object-cover" src="https://placehold.co/600x400" alt="Placeholder image">
											</else>
										</if>
									</div>
									<h3 class="font-semibold text-lg">
										<field name="title"></field>
									</h3>
									<p class="text-base-content/75 mb-3">
										<field name="publish_date" date_format="F j, Y"></field>
									</p>
									<a class="inline-flex items-center gap-2 font-semibold link link-hover" href="{Field url}">
										Read the full article
										<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 16 16">
											<path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"></path>
										</svg>
									</a>
								</div>

							</div>
						</loop>
						<div class="flex justify-center lg:justify-end">
							<!-- pagination -->
							<paginatebuttons></paginatebuttons>
						</div>
					</div>
				</tangible>`
			},

			{
				name: "Two Columns Variant",
				icon_html: `<i aria-hidden="true" class="fa fa-th-large"></i>`,
				template_html: `<tangible class="live-refresh">
					<div class="flex flex-col-reverse gap-3">
						<loop class="grid gap-6 mb-4 md:grid-cols-2" type="post" paged="4" orderby="date" order="desc">
							<div class="relative">
								<div class="grid gap-3">
									<div>
										<if field="image">
											<img loading="lazy" src="{Field image_url size='large'}" srcset="{Field image_srcset}" sizes="{Field image_sizes}" class="w-full object-cover rounded" style="aspect-ratio:4/3" alt="{Field image_alt}">
											<else>
												<img loading="lazy" src="https://placehold.co/400x300" class="w-full object-cover rounded" style="aspect-ratio:4/3" alt="Placeholder">
											</else>
										</if>
									</div>

									<div>
										<div>
											<span class="text-base-content/75 mb-1 block">
												<field name="publish_date" date_format="F j, Y">
												</field>
											</span>

											<h3 class="font-bold text-xl">
												<field name="title">
												</field>
											</h3>

											<field name="excerpt" words="30" auto="true">



												<a href="{Field url}" class="link link-hover">Read More...</a>

											</field>
										</div>
									</div>
								</div>
							</div>
						</loop>
						<div class="flex justify-center lg:justify-end">
							<!-- pagination -->
							<paginatebuttons></paginatebuttons>
						</div>
					</div>
				</tangible>`
			},
			{
				name: "Two Col w/Author",
				icon_html: `<i aria-hidden="true" class="fa fa-id-card-o"></i>`,
				template_html: `<tangible class="live-refresh">
		<div id="mygetposts">
			<loop class="grid gap-6 mb-4 md:grid-cols-2" type="post" paged="4" orderby="date" order="desc">
				<div class="relative">
					<div class="grid gap-3 lg:grid-cols-12">
						<div class="lg:col-span-4">
							<if field="image">

								<img loading="lazy" src="{Field image_url size='medium'}" srcset="{Field image_srcset}" sizes="{Field image_sizes}" class="w-full h-64 object-cover" alt="{Field image_alt}">

								<else>
									<img loading="lazy" src="https://placehold.co/600x400?text=Placeholder" class="w-full h-64 object-cover" alt="Placeholder image">
								</else>
							</if>
						</div>

						<div class="lg:col-span-8">
							<div class="mb-3">
								<p class="text-sm mb-1">Posted on the: <span class="text-base-content/50">
										<small class="text-base-content/60">
											<field name="publish_date" date_format="F j, Y">
											</field>
										</small>
									</span></p>
								<a class="link link-hover" href="{Field url}">
									<h3 class="font-bold text-lg">
										<field name="title"></field>
									</h3>
								</a>
							</div>
							<div class="flex items-center gap-2">
								<if field="author_avatar">
									<img loading="lazy" class="rounded-full shadow border-2 border-base-300" src="{Field author_avatar_url size='24'}" alt="{Field author_full_name}">
								</if>
								<span class="font-semibold">
									<field name="author_full_name"></field>
								</span>
							</div>
						</div>
					</div>
				</div>
			</loop>
		</div>
		<!-- pagination -->
		<div class="flex justify-center">
			<paginatebuttons></paginatebuttons>
		</div>
	</tangible>`
			},

			{
				name: "Three Columns",
				icon_html: `<i aria-hidden="true" class="fa fa-table"></i>`,
				template_html: `<tangible class="live-refresh">
					<div class="flex flex-col-reverse lg:flex-col gap-3">
						<loop class="grid gap-6 mb-4 md:grid-cols-3" type="post" paged="6" orderby="date" order="desc">
							<div class="relative">
								<div class="grid gap-3">
									<div>
										<if field="image">
											<img loading="lazy" src="{Field image_url size='large'}" srcset="{Field image_srcset}" sizes="{Field image_sizes}" class="w-full object-cover rounded" style="aspect-ratio:4/3" alt="{Field image_alt}">
											<else>
												<img loading="lazy" src="https://placehold.co/300x200" class="w-full object-cover rounded" style="aspect-ratio:4/3" alt="Placeholder">
											</else>
										</if>
									</div>

									<div>
										<div>
											<h3 class="font-bold text-lg">
												<field name="title">
												</field>
											</h3>

											<span class="text-base-content/75 mb-1 block">
												<field name="publish_date" date_format="F j, Y">
												</field>
											</span>




										</div>
									</div>
								</div>
							</div>
						</loop>
						<div class="flex justify-center lg:justify-end">
							<!-- pagination -->
							<paginatebuttons></paginatebuttons>
						</div>
					</div>
				</tangible>`
			},

		 



			{
				name: "Three Columns Variant",
				icon_html: `<i aria-hidden="true" class="fa fa-th"></i>`,
				template_html: `<tangible class="live-refresh">

				<loop class="grid gap-6 mb-4 md:grid-cols-2 xl:grid-cols-3" type="post" paged="6" orderby="date" order="desc">
					<div class="relative">
						<div class="grid gap-3">
							<div>
								<if field="image">

									<img loading="lazy" src="{Field image_url size='large'}" srcset="{Field image_srcset}" sizes="{Field image_sizes}" class="mb-3 w-full object-cover rounded-xl shadow" style="aspect-ratio:4/3" alt="{Field image_alt}">

									<else>
										<img loading="lazy" src="https://placehold.co/300x200" class="mb-3 w-full object-cover rounded-xl shadow" style="aspect-ratio:4/3" alt="Placeholder">
									</else>
								</if>
							</div>
							<div>
								<a class="link link-hover no-underline" href="{Field url}">
									<h3 class="font-bold text-2xl tracking-tight">
										<field name="title">
										</field>
									</h3>
								</a>
								<p class="text-base-content/50">
									<field name="publish_date" date_format="F j, Y">
									</field>
								</p>
							</div>
						</div>
					</div>
				</loop>
				<!-- pagination -->
				<div class="flex justify-center">
					<paginatebuttons></paginatebuttons>
				</div>
			</tangible>`
			},

			{
				name: "Three-Column Smaller",
				icon_html: `<i aria-hidden="true" class="fa fa-th-list"></i>`,
				template_html: `<tangible class="live-refresh">
				<loop class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3" paged="3" type="post" orderby="date" order="desc">
					<div class="post relative max-w-md">

						<div class="aspect-[21/9] mb-3 overflow-hidden rounded">
							<if field="image">
								<!-- If the post has a featured image -->
								<img class="w-full h-full object-cover" src="{Field image_url size='large'}" alt="{Field image_alt}">
								<else>
									<!-- If no image, use placeholder -->
									<img class="w-full h-full object-cover" src="https://placehold.co/600x400" alt="Placeholder image">
								</else>
							</if>
						</div>

						<div>
							<h3 class="font-semibold text-lg mb-2">
								<field name="title"></field>
							</h3>
							<p class="text-base-content/75">
								<field name="publish_date" date_format="F j, Y"></field>
							</p>
						</div>

						<!-- Full card clickable link -->
						<a class="absolute inset-0" href="{Field url}" aria-label="Read post"></a>

					</div>

				</loop>

				<div>
					<paginatebuttons></paginatebuttons>
				</div>

			</tangible>`
			},

			{
				name: "Four Columns w/ Dark Overlay",
				icon_html: `<i aria-hidden="true" class="fa fa-adjust"></i>`,
				template_html: `<tangible class="live-refresh">
				<loop class="grid gap-6 md:grid-cols-2 lg:grid-cols-4" paged="4" type="post" orderby="date" order="desc">
					<div class="post">
						<div class="relative flex items-end justify-center h-full min-h-[364px]">

							<if field="image">
								<!-- If the post has a featured image -->
								<img class="absolute inset-0 w-full h-full object-cover rounded" src="{Field image_url size='large'}" alt="{Field image_alt}">
								<else>
									<!-- If no image, use placeholder -->
									<img class="absolute inset-0 w-full h-full object-cover rounded" src="https://placehold.co/600x400" alt="Placeholder image">
								</else>
							</if>

							<div class="card bg-slate-900/90 text-slate-200 border-0 mb-3 mx-2">
								<div class="card-body">
									<p class="text-slate-300/75 mb-2">
										<field name="publish_date" date_format="F j, Y"></field>
									</p>
									<h3 class="font-semibold text-sm">
										<field name="title"></field>
									</h3>
								</div>
							</div>

							<!-- Full clickable overlay -->
							<a class="absolute inset-0" href="{Field url}" aria-label="Read post"></a>

						</div>
					</div>
				</loop>
				<div>
					<paginatebuttons></paginatebuttons>
				</div>
			</tangible>`
			},

			{
				name: "Four Columns w/ Light Overlay",
				icon_html: `<i aria-hidden="true" class="fa fa-sun-o"></i>`,
				template_html: `<tangible class="live-refresh">
				<loop class="grid gap-6 md:grid-cols-2 lg:grid-cols-4" paged="4" type="post" orderby="date" order="desc">
					<div class="post">
						<div class="relative flex items-end h-full min-h-[364px]">

							<if field="image">
								<!-- If the post has a featured image -->
								<img class="absolute inset-0 w-full h-full object-cover rounded" src="{Field image_url size='large'}" alt="{Field image_alt}">
								<else>
									<!-- If no image, use placeholder -->
									<img class="absolute inset-0 w-full h-full object-cover rounded" src="https://placehold.co/600x400" alt="Placeholder image">
								</else>
							</if>

							<div class="card bg-base-100/80 border-0 mb-3 mx-2 w-full">
								<div class="card-body">
									<p class="text-base-content/75 mb-2">
										<field name="publish_date" date_format="F j, Y"></field>
									</p>
									<h3 class="font-bold text-sm">
										<field name="title"></field>
									</h3>
								</div>
							</div>

							<!-- Full overlay link -->
							<a class="absolute inset-0" href="{Field url}" aria-label="Read post"></a>

						</div>
					</div>
				</loop>
				<div>
					<paginatebuttons></paginatebuttons>
				</div>
			</tangible>`
			},

			{
				name: "Full-Width w/ Transp. Overlay",
				icon_html: `<i aria-hidden="true" class="fa fa-arrows-alt"></i>`,
				template_html: `<tangible class="live-refresh">


			<div id="sc-single-article-loop-RANDOMID">

				<loop type="post" count="3" orderby="date" order="desc">
					<div class="sc_full_post relative">
						<div class="absolute inset-0 bg-base-100/25" style="isolation:isolate">
							<if field="image">
								<!-- If the post has a featured image -->
								<img style="mix-blend-mode: overlay;" class="w-full h-full object-cover" src="{Field image_url size='full'}" alt="{Field image_alt}">
								<else>
									<!-- If no image, use placeholder with blend mode -->
									<img style="mix-blend-mode: overlay;" class="w-full h-full object-cover" src="https://placehold.co/600x400" alt="Placeholder image">
								</else>
							</if>
						</div>

						<div class="container mx-auto py-12 xl:py-16">
							<div class="grid lg:grid-cols-12">
								<div class="card lg:col-span-6 lg:col-start-2 max-w-2xl bg-base-100/80">
									<div class="card-body py-4">
										<p class="block text-lg text-base-content/75 mb-4">
											<field name="publish_date" date_format="F j, Y"></field>
										</p>
										<h3 class="text-4xl font-bold mb-4">
											<field name="title"></field>
										</h3>
										<div class="text-lg">
											<field name="excerpt" words="20" suffix="..."></field>
										</div>
									</div>
									<div class="card-actions px-6 pb-6">
										<a class="inline-flex items-center gap-2 link link-hover" href="{Field url}">
											Read the full article
											<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 16 16">
												<path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"></path>
											</svg>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</loop>
			</div>

		</tangible>`
			},
 
			{
				name: "Carousel",
				icon_html: `<i aria-hidden="true" class="fa fa-sliders"></i>`,
				template_html: `<tangible class="live-refresh">
					<!-- DaisyUI carousel populated by a WP post loop -->
					<div class="carousel carousel-vertical rounded-box h-96 w-full">
						<loop type="post" post_type="post" count="6" orderby="date" order="desc">
							<div id="item-{Field id}" class="carousel-item h-full">
								<div class="aspect-square md:aspect-[21/9] mb-3 overflow-hidden rounded w-full">
									<if field="image">
										<!-- IF: the post has a featured image -->
										<a class="" href="{Field url}"> <img class="w-full h-full object-cover" loading="lazy" src="{Field image_url size='large'}" srcset="{Field image_srcset}" sizes="{Field image_sizes}" alt="{Field image_alt}"></a>
										<else>
											<!-- ELSE: fallback image when there's no featured image -->
											<a class="" href="{Field url}"> <img class="w-full h-full object-cover" loading="lazy" src="https://placehold.co/1200x600" alt="Placeholder"></a>
										</else>
									</if>

								</div>

							</div>
						</loop>
					</div>
				</tangible>`
			},

		 

			{
				name: "Accordion ",
				icon_html: `<i aria-hidden="true" class="fa fa-list-ul"></i>`,
				template_html: `<tangible class="live-refresh">
					<div class="join join-vertical w-full">
						<loop type="post" count="6" order="desc" orderby="date" >
							<div class="collapse collapse-arrow join-item border-b border-base-300">
								<input type="checkbox">
								<div class="collapse-title text-base font-semibold">
									<field name="title"></field>
								</div>
								<div class="collapse-content">
									<field name="content"></field>
								</div>
							</div>
						</loop>
					</div>
				</tangible>`
			},

			 
			{
				name: "Accordion - First El Open ",
				icon_html: `<i aria-hidden="true" class="fa fa-indent"></i>`,
				template_html: `
				 <tangible class="live-refresh">
					<div class="join join-vertical w-full">
						<loop order="desc" orderby="date" paged="6" type="post">
							<if first="">
							<div class="collapse collapse-arrow join-item border-b border-base-300">
								<input type="radio" name="accordion-tone" checked="checked">
								<div class="collapse-title text-base font-semibold">
									<field name="title">
									</field>
								</div>
								<div class="collapse-content">
									<field name="content">
									</field>
								</div>
							</div>
							<else>
							<div class="collapse collapse-arrow join-item border-b border-base-300">
								<input type="radio" name="accordion-tone">
								<div class="collapse-title text-base font-semibold">
									<field name="title">
									</field>
								</div>
								<div class="collapse-content">
									<field name="content">
									</field>
								</div>
							</div>
						</loop>
					</div>
				</tangible>
				`
			},

			{
				name: "Vertical Timeline",
				icon_html: `<i aria-hidden="true" class="fa fa-history"></i>`,
				template_html: `<tangible class="live-refresh">
					<ul class="timeline timeline-vertical">
						<loop order="desc" orderby="date" type="post" count="6">
							<li>

								<If not first>
									<hr />
								</If>

								<div class="timeline-start">
									<field name="publish_date" date_format="F j, Y"></field>
								</div>

								<div class="timeline-middle">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
										<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
									</svg>
								</div>

								<div class="timeline-end timeline-box">
									<a href="{Field url}">
										<field name="title"></field>
									</a>
								</div>

								<If not last>
									<hr />
								</If>

							</li>
						</loop>
					</ul>
				</tangible>`
			},
			{
				name: "Vertical timeline with right side only",
				icon_html: `<i aria-hidden="true" class="fa fa-history"></i>`,
				template_html: `<tangible class="live-refresh">
					<ul class="timeline timeline-vertical">
						<loop order="asc" type="post" count="6">
							<li>
								<If not first>
									<hr />
								</If>

								<div class="timeline-middle">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
										<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
									</svg>
								</div>

								<div class="timeline-end timeline-box">
									<a href="{Field url}">
										<field name="title"></field>
									</a>
								</div>

								<If not last>
									<hr />
								</If>
							</li>
						</loop>
					</ul>
				</tangible>`
			},

			{
				name: "Vertical timeline with left side only",
				icon_html: `<i aria-hidden="true" class="fa fa-history"></i>`,
				template_html: `<tangible class="live-refresh">
					<ul class="timeline timeline-vertical">
						<loop order="asc" type="post" count="6">
							<li>
								<If not first>
									<hr />
								</If>

								<div class="timeline-start timeline-box">
									<a href="{Field url}">
										<field name="title"></field>
									</a>
								</div>

								<div class="timeline-middle">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
										<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
									</svg>
								</div>

								<If not last>
									<hr />
								</If>
							</li>
						</loop>

					</ul>
				</tangible>`
			},
			{
				name: "Vertical timeline with left side only",
				icon_html: `<i aria-hidden="true" class="fa fa-history"></i>`,
				template_html: `<tangible class="live-refresh">
			<ul class="timeline timeline-snap-icon max-md:timeline-compact timeline-vertical timeline-alternating">

				<loop order="asc" orderby="date" type="post" count="5">

					<li>

						<If count is_not value="1">
							<hr />
						</If>

						<div class="timeline-middle">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
								<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
							</svg>
						</div>

						<div class="timeline-start mb-10 md:text-end">
							<time class="font-mono italic">
								<field name="date" date_format="Y"></field>
							</time>

							<div class="text-lg font-black">
								<field name="title"></field>
							</div>

							<Format words="30">
								<field name="content"></field>
							</Format>
						</div>

						<If not last>
							<hr />
						</If>

					</li>

				</loop>

			</ul>
		</tangible>`
			},

			

			{
				name: "Tabs",
				icon_html: `<i aria-hidden="true" class="fa fa-table"></i>`,
				template_html: `<tangible class="live-refresh">
		<!-- name of each tab group should be unique -->
		<div class="tabs tabs-border">
			<loop type="post" count="3" order="desc" orderby="date">
				<if first="">
					<input type="radio" name="my_tabs-zoro" class="tab" aria-label="{Field title}" checked="checked">
					<else>
						<input type="radio" name="my_tabs-zoro" class="tab" aria-label="{Field title}">
					</else>
				</if>
				<div class="tab-content border-base-300 bg-base-100 p-10">
					<h3 class="text-lg font-black mb-2">
						<field name="title"></field>
					</h3>

					<format words="30">
						<field name="content"></field>
					</format>
				</div>

			</loop>

		</div>
	</tangible>
</div>`
			},
		],
	},


	"WordPress Integration": {
		description: "Dynamic snippets that connect to WordPress content and APIs.",
		blocks: [
			{
				name: "Shortcode",
				icon_html: `<i aria-hidden="true" class="fa fa-code"></i>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[lc_dummyshortcode]</div>`
			},
			{
				name: "Get Posts",
				icon_html: `<i aria-hidden="true" class="fa fa-newspaper-o"></i>`,
				template_html: `<tangible class="live-refresh"> <div class="grid lg:flex items-start gap-6"> <loop class="grid gap-3 max-w-4xl mx-auto w-full" type="post" paged="4" orderby="date" order="desc"> <div class="card card-side shadow-sm"> <figure> <if field="image"> <img class="w-24 h-24 object-cover" loading="lazy" src="{Field image_url size='thumbnail'}" srcset="{Field image_srcset}" sizes="{Field image_sizes}" alt="{Field image_alt}"> <else> <img class="w-24 h-24 object-cover" loading="lazy" src="https://placehold.co/150" alt="Placeholder"> </else> </if> </figure> <div class="card-body"> <h2 class="card-title"> <field name="title"> </field> </h2> <p> <field name="publish_date" date_format="F j, Y"> </field> </p> <div class="card-actions justify-end"> <a class="btn btn-primary" href="{Field url}">Read</a> </div> </div> </div> </loop> <div class="sticky top-5 shrink-0"> <!-- pagination --> <paginatebuttons></paginatebuttons> </div> </div> </tangible>`
			},
			{
				name: "Get Categories",
				icon_html: `<i aria-hidden="true" class="fa fa-folder-open"></i>`,
				template_html: `<tangible class="live-refresh"><div class="taxonomy-list"><loop hide_empty="true" taxonomy="category" type="taxonomy_term"><a class="badge rounded-pill bg-warning text-decoration-none" href="{Field url}"><field name="title"></field></a></loop></div></tangible>`
			},
			{
				name: "Get Tags",
				icon_html: `<i aria-hidden="true" class="fa fa-tags"></i>`,
				template_html: `<tangible class="live-refresh"><div class="taxonomy-list"><loop hide_empty="true" taxonomy="tag" type="taxonomy_term"><a class="badge rounded-pill bg-warning text-decoration-none" href="{Field url}"><field name="title"></field></a></loop></div></tangible>`
			},
			{
				name: "List Posts as Links",
				icon_html: `<i aria-hidden="true" class="fa fa-list-ul"></i>`,
				template_html: `<tangible class="live-refresh"> <ul class="list bg-base-100 rounded-box shadow-md"> <loop count="9" order="desc" orderby="date" type="post"> <li class="list-row "> <a href="{Field url}"> <field name="title"></field> </a> </li> </loop> </ul> </tangible>`
			},
			{
				name: "List Pages as Links",
				icon_html: `<i aria-hidden="true" class="fa fa-files-o"></i>`,
				template_html: `<tangible class="live-refresh"> <ul class="list bg-base-100 rounded-box shadow-md"> <loop count="9" order="desc" orderby="date" type="page"> <li class="list-row "> <a href="{Field url}"> <field name="title"></field> </a> </li> </loop> </ul> </tangible>`
			},
			{
				name: "Get Post Content",
				icon_html: `<i aria-hidden="true" class="fa fa-file-text-o"></i>`,
				template_html: `
					<!-- Replace "the_post_slug" with the slug of the post you want to recall -->
					<!-- This shortcode returns a specific post's content. Useful to grab LiveCanvas partials. Just pass the post id OR  the slug as parameter. -->
					<!-- https://docs.livecanvas.com/shortcodes/#lc-get-post -->
					<div class="live-shortcode" lc-helper="shortcode">[lc_get_post slug='the_post_slug']</div>`
			},
			{
				name: "Get GT Block Post Content",
				icon_html: `<i aria-hidden="true" class="fa fa-file-text-o"></i>`,
				template_html: `
					<!-- This shortcode returns a specific post's content, parsing Gutenberg blocks, if present.  -->
					<!-- https://docs.livecanvas.com/shortcodes/#lc-get-gt-block -->
					<div class="live-shortcode">[lc_get_gt_block id="33"]</div>
				`
			},
			{
				name: "Sidebar Widget Area",
				icon_html: `<i aria-hidden="true" class="fa fa-th-large"></i>`,
				template_html: `<tangible class="live-refresh">[lc_widgetsarea id="main-sidebar"]</tangible>`
			},
			{
				name: "Get Custom Field Value",
				icon_html: `<i aria-hidden="true" class="fa fa-database"></i>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[lc_get_cf field=field_name]</div>`
			},
			{
				name: "LiveCanvas Partial",
				icon_html: `<i aria-hidden="true" class="fa fa-clone"></i>`,
				template_html: `
					<!-- Replace "the_post_slug" with the slug of the post you want to recall -->
					<div class="live-shortcode" lc-helper="shortcode"> [lc_get_post post_type="lc_partial" slug="the_post_slug"] </div>`
			},
			{
				name: "Simple Contact Form",
				icon_html: `<i aria-hidden="true" class="fa fa-envelope"></i>`,
				template_html: `<form><div class="form-group mb-4"><label>Your Name (optional)</label><input autocomplete="off" class="form-control" hidden="" name="mouseglue" placeholder="John Doe" type="text" value=""/><input class="form-control" name="name" placeholder="John Doe" type="text" value=""/></div><div class="form-group mb-4"><label>Your Email Address</label><input class="form-control" name="email" placeholder="name@example.com" type="email" value=""/></div><div class="form-group mb-4"><label>Subject (optional)</label><input class="form-control" name="subject" placeholder="Contact Subject" type="text"/></div><div class="form-group mb-4"><label>Your Message</label><textarea class="form-control" maxlength="300" name="message" rows="3"></textarea></div><button class="btn btn-primary btn-lg" type="submit">Submit Form</button> [lc_form action="lc_submit_contact_form"] </form>`
			},
			{
				name: "Theme Partial File",
				icon_html: `<i aria-hidden="true" class="fa fa-file-code-o"></i>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[lc_get_template_part slug="partials/test-partial"]</div>`
			},
			{
				name: "AJAX/PHP Custom Form",
				icon_html: `<i aria-hidden="true" class="fa fa-cogs"></i>`,
				template_html: `<div class="lc-block"> <form class="card bg-base-100 shadow-xl p-6"> <div class="w-full mb-4"> <label class="label"> <span class="label-text font-medium">Your Name (optional)</span> </label> <input autocomplete="off" class="input input-bordered w-full" hidden="" name="mouseglue" placeholder="John Doe" type="text" value=""> <input class="input input-bordered w-full" name="name" placeholder="John Doe" type="text" value=""> </div> <div class="w-full mb-4"> <label class="label"> <span class="label-text font-medium">Your Email Address</span> </label> <input class="input input-bordered w-full" name="email" placeholder="name@example.com" type="email" value=""> </div> <div class="w-full mb-4"> <label class="label"> <span class="label-text font-medium">Subject (optional)</span> </label> <input class="input input-bordered w-full" name="subject" placeholder="Contact Subject" type="text"> </div> <div class="w-full mb-4"> <label class="label"> <span class="label-text font-medium">Your Message</span> </label> <textarea class="textarea textarea-bordered w-full h-32" maxlength="300" name="message" placeholder="Write your message here..."></textarea> </div> <div class="mt-6"> <button class="btn btn-primary btn-lg w-full" type="submit">Submit Form</button> </div> [lc_form action="lc_submit_contact_form"] </form> </div>`
			},
			{
				name: "Login Form",
				icon_html: `<i aria-hidden="true" class="fa fa-sign-in"></i>`,
				template_html: `<div class="live-shortcode convertible-shortcode" lc-helper="shortcode">[lc_insert snippet="forms/login"]</div>`
			},
			{
				name: "Registration Form",
				icon_html: `<i aria-hidden="true" class="fa fa-user-plus"></i>`,
				template_html: `<div class="live-shortcode convertible-shortcode" lc-helper="shortcode">[lc_insert snippet="forms/registration"]</div>`
			},
			{
				name: "Search Form",
				icon_html: `<i aria-hidden="true" class="fa fa-search"></i>`,
				template_html: `<form action="/" class="d-flex ms-auto justify-content-center" id="searchform" method="get" role="search"><div class="input-group"><input aria-label="Search" class="form-control" id="s" name="s" placeholder="Search..." type="text" value=""/><button aria-label="Submit search" class="btn btn-outline-secondary" id="searchsubmit" type="submit"><svg class="text-dark" fill="currentColor" height="1rem" lc-helper="svg-icon" viewBox="0 0 24 24" width="1rem" xmlns="http://www.w3.org/2000/svg"><path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"></path></svg></button></div></form>`
			},
			
		],
	},
	"Post Data": {
		description: "Single-post metadata helpers for titles, dates, and related fields.",
		blocks: [
			{
				name: "Page Title",
				icon_html: `<i aria-hidden="true" class="fa fa-header"></i>`,
				template_html: `<tangible class="live-refresh"><!-- the title --><h1 class="text-4xl font-bold"><field name="title"></field></h1></tangible>`
			},
			{
				name: "Page ID",
				icon_html: `<i aria-hidden="true" class="fa fa-hashtag"></i>`,
				template_html: `<tangible class="live-refresh"><field name="id"></field></tangible>`
			},
			{
				name: "Page Date",
				icon_html: `<i aria-hidden="true" class="fa fa-calendar"></i>`,
				template_html: `<tangible class="live-refresh"><field name="publish_date" date_format="F j, Y"></field></tangible>`
			},
			{
				name: "Author Name",
				icon_html: `<i aria-hidden="true" class="fa fa-user"></i>`,
				template_html: `<tangible class="live-refresh"><field name="author_full_name"></field></tangible>`
			},
			{
				name: "Author Avatar",
				icon_html: `<i aria-hidden="true" class="fa fa-user-circle-o"></i>`,
				template_html: `<tangible class="live-refresh"><!-- Author Avatar --> <if field="author_avatar"> <div class="avatar"> <div class="w-12 rounded-full"> <img alt="{Field author_full_name}" src="{Field author_avatar_url size='72'}"> </div> </div> </if> </tangible>`
			},
			{
				name: "Link to Page URL",
				icon_html: `<i aria-hidden="true" class="fa fa-link"></i>`,
				template_html: `<tangible class="live-refresh"><a class="link link-primary" href="{Field url}"><field name="title"></field></a></tangible>`
			},
			{
				name: "Custom Field Value",
				icon_html: `<i aria-hidden="true" class="fa fa-database"></i>`,
				template_html: `<tangible class="live-refresh"><field name="mycustomfield"></field></tangible>`
			}, 
			{
				name: "Featured Image",
				icon_html: `<i aria-hidden="true" class="fa fa-picture-o"></i>`,
				template_html: `<tangible class="live-refresh"> <if field="image"> <img alt="{Field image_alt}" class="rounded-box" sizes="{Field image_sizes}" src="{Field image_url size='medium'}" srcset="{Field image_srcset}"> <else> <img alt="Placeholder image" class="w-full rounded-box" src="https://placehold.co/600x400?text=Placeholder"> </else> </if> </tangible>`
			},
			/*
			{
				name: "Simple Featured Image w/URL",
				icon_html: `<i aria-hidden="true" class="fa fa-file-image-o"></i>`,
				template_html: `<tangible class="live-refresh"><img alt="{Field image_alt}" src="{Field image_url}"></tangible>`
			},
			{
				name: "Sharing Buttons",
				icon_html: `<i aria-hidden="true" class="fa fa-share-alt"></i>`,
				template_html: `<tangible class="live-refresh">[lc_the_sharing]</tangible>`
			},  
			{
				name: "Comments Form",
				icon_html: `<i aria-hidden="true" class="fa fa-comments"></i>`,
				template_html: `<tangible class="live-refresh"><template name="comments" theme="part"></template></tangible>`
			},*/

		],
	},

	"Site Data": {
		description: "Global site information such as names, URLs, and helpers.",
		blocks: [
			{
				name: "Site Name",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<tangible class="live-refresh"><span class="text-lg font-semibold"><site name=""></site></span></tangible>`
			},
			{
				name: "Site Tagline",
				icon_html: `<i aria-hidden="true" class="fa fa-quote-left"></i>`,
				template_html: `<tangible class="live-refresh"><span class="text-base-content/70"><site description=""></site></span></tangible>`
			},
			{
				name: "Site URL",
				icon_html: `<i aria-hidden="true" class="fa fa-link"></i>`,
				template_html: `<tangible class="live-refresh"><span class="link link-hover"><site url=""></site></span></tangible>`
			},
			{
				name: "Site Logo",
				icon_html: `<i aria-hidden="true" class="fa fa-picture-o"></i>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[lc_custom_logo]</div>`
			},
			{
				name: "Responsive Navigation",
				icon_html: `<i aria-hidden="true" class="fa fa-bars"></i>`,
				template_html: `
					<nav class="navbar bg-base-100">
						<div class="navbar-start">
							<div class="dropdown">
								<label tabindex="0" class="btn btn-ghost lg:hidden">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
									</svg>
								</label>
								<div tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
									<div lc-helper="shortcode" class="live-shortcode">[lc_nav_menu theme_location="primary" container_class="" container_id="" menu_class="menu menu-sm"]</div>
								</div>
							</div>
						</div>
						<div class="navbar-center hidden lg:flex">
							<div lc-helper="shortcode" class="live-shortcode">[lc_nav_menu theme_location="primary" container_class="" container_id="" menu_class="menu menu-horizontal"]</div>
						</div>
					</nav>
				`
			},
			{
			name: "Login Button",
				icon_html: `<i aria-hidden="true" class="fa fa-sign-in"></i>`,
				template_html: `<tangible class="live-refresh"><a href="{Url login}" class="btn btn-primary">Login</a></tangible>`
			},
			{
				name: "Logout Button",
				icon_html: `<i aria-hidden="true" class="fa fa-sign-out"></i>`,
				template_html: `<tangible class="live-refresh"><a href="{Url logout}" class="btn btn-primary">Logout</a></tangible>`
			},
			{
				name: "Register Button",
				icon_html: `<i aria-hidden="true" class="fa fa-user-plus"></i>`,
				template_html: `<tangible class="live-refresh"><a href="{Url register}" class="btn btn-primary">Register</a></tangible>`
			},
			{
				name: "Current Date",
				icon_html: `<i aria-hidden="true" class="fa fa-calendar"></i>`,
				template_html: `<tangible class="live-refresh"><Date format="F j, Y" /></tangible>`
			},
			{
				name: "Current Year",
				icon_html: `<i aria-hidden="true" class="fa fa-copyright"></i>`,
				template_html: `<tangible class="live-refresh"><Date format="Y" /></tangible>`
			},
		],
	},
	"ACF not tested": {
		description: "Advanced Custom Fields integrations for pulling structured data.",
		blocks: [
			{
				"name": "Text",
				"icon_html": `<i aria-hidden='true' class='fa fa-font'></i>`,
				"template_html": `<tangible class='live-refresh'>\n<!-- Replace "my_text_field" with your custom field name --><field acf_text='my_text_field'></field></tangible>`
			},
			{
				"name": "Textarea",
				"icon_html":  `<i aria-hidden='true' class='fa fa-align-left'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_textarea_field" with your textarea custom field name --><field acf_textarea='my_textarea_field'></field></tangible>`
			},
			{
				"name": "Number",
				"icon_html":  `<i aria-hidden='true' class='fa fa-hashtag'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_number_field" with your number custom field name --><field acf_number='my_number_field'></field></tangible>`
			},
			{
				"name": "Range",
				"icon_html":  `<i aria-hidden='true' class='fa fa-sliders'></i>`,
				"template_html": `<tangible class='live-refresh'> \n<!-- Replace "my_range_field" with your range custom field name --> <set name='percentage'> <field name='my_range_field'> </field> </set> <div class='space-y-2'> <progress class='progress w-full' value='{Get percentage}' max='100'></progress> <div class='text-sm text-base-content/70'><get name='percentage'></get>%</div> </div></tangible>`
			},
			{
				"name": "Email",
				"icon_html":  `<i aria-hidden='true' class='fa fa-envelope'></i>`,
				"template_html":  `<tangible class='live-refresh'> \n<!-- Replace "my_email_field" with your email custom field name --> <a class='link link-hover' href='mailto:{Field acf_email=my_email_field}'><field acf_email='my_email_field'></field></a></tangible>`
			},
			{
				"name": "URL",
				"icon_html":  `<i aria-hidden='true' class='fa fa-link'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_url_field" with your URL custom field name --> \n <a class='link link-hover' href='{Field acf_url=my_url_field}' target='_blank'>Link</a></tangible>`
			},
			{
				"name": "Image (IMG tag)", 
				"icon_html":  `<i aria-hidden='true' class='fa fa-image'></i>`,
				"template_html":  `
					<tangible class="live-refresh">
						<!-- Replace "my_image_field" with your custom field name -->
						<set img_field="">my_image_field</set>

						<if field="{Get img_field}">
							<loop acf_image="{Get img_field}">
								<img class="w-full h-auto" src="{Field url size=large}" srcset="{Field srcset}" sizes="{Field sizes}" alt="{Field alt}">
							</loop>
						</if>

					</tangible>
				`
			},
			{
				"name": "Background Image (DIV tag)",
				"problem": "fix variable ACF field",
				"icon_html":  `<i style='color: var(--color-grey-2)' aria-hidden='true' class='fa fa-image'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_image_field" with your ACF image custom field name --><if field='my_image_field'><div class='rounded-xl shadow' style='background-image: url({Field acf_image=my_image_field field=url size=large}); min-height:300px;background-size:cover;'></div><else><div class='bg-base-200 rounded-xl shadow flex items-center justify-center' style='min-height:300px;background-size:cover'>Placeholder</div></else></if></tangible>`
			},
			{
				"name": "File (download link)",
				"icon_html":  `<i aria-hidden='true' class='fa fa-download'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_file_field" with your file custom field name --><if field='my_file_field'><a class='btn btn-primary' href='{Field acf_file=my_file_field field=url}' download>Download File</a></if></tangible>`
			},
			{
				"name": "WYSIWYG",
				"icon_html":  `<i aria-hidden='true' class='fa fa-paragraph'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_wysiwyg_field" with your wysiwyg custom field name --><field acf_wysiwyg='my_wysiwyg_field'></field></tangible>`
			},
			{
				"name": "oEmbed Responsive Embed",
				"problem": "responsiveness",
				"icon_html":  `<i aria-hidden='true' class='fa fa-youtube-play'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_oembed_field" with your oembed custom field name --><div class="aspect-video w-full"><field acf_oembed='my_oembed_field'></field></div></tangible>`
			},
			{
				"name": "Gallery",
				"icon_html":  `<div style='display: flex; align-items: center; gap: 0.25rem;'><i style='font-size: 0.60rem;' aria-hidden='true' class='fa fa-file-image-o'></i><i style='font-size: 0.60rem;' aria-hidden='true' class='fa fa-file-image-o'></i><i style='font-size: 0.60rem;' aria-hidden='true' class='fa fa-file-image-o'></i></div>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_gallery_field" with your ACF Gallery custom field name --><div class='grid gap-4 sm:grid-cols-2 lg:grid-cols-4'><loop acf_gallery='my_gallery_field'><img src='{Field url size=medium}' alt='{Field alt}' class='w-full rounded-box'></loop></div></tangible>`
			},
			{
				"name": "Gallery with Full Image Links",
				"icon_html":  `<div style='display: flex; align-items: center; gap: 0.25rem;'><i style='font-size: 0.60rem;' aria-hidden='true' class='fa fa-file-image-o'></i><i style='font-size: 0.60rem;' aria-hidden='true' class='fa fa-file-image-o'></i><i style='font-size: 0.60rem;' aria-hidden='true' class='fa fa-file-image-o'></i></div>`,
				"template_html":  ` 
					<tangible class="live-refresh">
						\n<!-- Replace "my_gallery_field" with your gallery custom field name -->
						<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
							<loop acf_gallery="my_gallery_field">
								<div>
									<a class="glightbox" href="{Field url size=full}">
										<img src="{Field url size=medium}" alt="{Field alt}" class="w-full rounded-box">
									</a>
								</div>
							</loop>
						</div>
					</tangible>
				`
			},
			{
				"name": "Gallery Bootstrap Carousel",
				"icon_html":  `<div style='display: flex; align-items: center; gap: 0.25rem;'><i style='font-size: 0.60rem;' aria-hidden='true' class='fa fa-caret-left'></i><i style='font-size: 0.75rem;' aria-hidden='true' class='fa fa-file-image-o'></i><i style='font-size: 0.60rem;' aria-hidden='true' class='fa fa-caret-right'></i></div>`,
				"template_html": `<tangible class='live-refresh'>
					<!-- DaisyUI carousel from ACF Gallery field "my_gallery_field" -->
					<div id='acfGalleryCarousel-RANDOMID' class='carousel w-full'>
						\n<!-- Replace "my_gallery_field" with your ACF gallery field name -->
						<loop acf_gallery='my_gallery_field'> 
							<div id='acf-slide-{Get loop=count}' class='carousel-item relative w-full'>
								<img src="{Field url size='large'}" srcset="{Field image_srcset}" sizes="{Field image_sizes}" class='w-full rounded-box shadow' style='aspect-ratio:16/9;object-fit:cover' alt="{Field alt}">
							</div>
						</loop>
						<!-- if: gallery is empty, show a fallback image -->
						<if previous_total='' value='0'>
							<div class='carousel-item relative w-full'>
								<img src='https://placehold.co/1200x675?text=No+images' class='w-full rounded-box' style='aspect-ratio:16/9;object-fit:cover' alt='Placeholder'>
							</div>
						</if>
					</div>
					<div class='flex justify-center w-full py-2 gap-2'>
						<loop acf_gallery='my_gallery_field'>
							<a href='#acf-slide-{Get loop=count}' class='btn btn-xs'>{Get loop=count}</a>
						</loop>
					</div>
				</tangible>`
			},
			{
				"name": "Select",
				"icon_html":  `<i aria-hidden='true' class='fa fa-list'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_select_field" with your custom field name --><field acf_select='my_select_field'></field></tangible>`
			},
			{
				"name": "True/False",
				"icon_html":  `<i aria-hidden='true' class='fa fa-toggle-on'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_true_false_field" with your custom field name --><if acf_true_false='my_true_false_field'> ✔️ <else> ❌ </else></if></tangible>`
			},			
			{
				"name": "Google Maps Address",
				"icon_html":  `<i aria-hidden='true' class='fa fa-map'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_acf_map_field" with your custom field name --><loop field='my_acf_map_field'> <p class='mb-1'>Address: <span class='text-base-content/75'> <field name='address'></field> </span></p> </loop></tangible>`
			},
			{
				"name": "Google Maps Button",
				"icon_html":  `<i aria-hidden='true' class='fa fa-map'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_acf_map_field" with your custom field name --><div class='lc-block' id='map-block-RANDOMID'> <loop field='my_acf_map_field'> <p class='font-bold mb-1'>Address</p> <p class='mb-2 js-address'> <field address=''></field> </p> <a class='btn btn-sm btn-outline js-gmaps' href='#' target='_blank' rel='noopener'> Open in Google Maps </a> </loop> <script> (function(){ var wrap = document.getElementById('map-block-RANDOMID'); if(!wrap) return; var addrEl = wrap.querySelector('.js-address'); var linkEl = wrap.querySelector('.js-gmaps'); if(addrEl && linkEl){ var q = encodeURIComponent(addrEl.textContent.trim()); linkEl.href = 'https://www.google.com/maps/search/?api=1&query=' + q; } })(); </script> </div></tangible>`
			},
			{
				"name": "Google Map in Iframe beta",
				"problem": "blank iframe and preview",
				"icon_html":  `<i class='fa fa-map' aria-hidden='true'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_acf_map_field" with your custom field name --><div class='lc-block aspect-video w-full' id='map-embed-RANDOMID'> <loop field='my_acf_map_field'> <p class='sr-only js-address-embed'> <field name='address'></field> </p> <iframe class='js-iframe' width='100%' height='100%' style='border:0;' loading='lazy' referrerpolicy='no-referrer-when-downgrade' allowfullscreen=''></iframe> </loop> <script> (function(){ var wrap = document.getElementById('map-embed-RANDOMID'); if(!wrap) return; var addrEl = wrap.querySelector('.js-address-embed'); var iframe = wrap.querySelector('.js-iframe'); if(addrEl && iframe){ var q = encodeURIComponent(addrEl.textContent.trim()); iframe.src = 'https://www.google.com/maps?q=' + q + '&z=15&output=embed'; iframe.title = 'Map: ' + addrEl.textContent.trim(); } })(); </script> </div></tangible>`
			},
			{
				"name": "Date Picker",
				"icon_html":  `<i aria-hidden='true' class='fa fa-calendar'></i>`,
				"template_html":  `<tangible class='live-refresh'> \n<!-- Replace "my_custom_date_field" with your custom field name --><if field="my_custom_date_field"> <field acf_date_picker="my_custom_date_field"></field> </if> </tangible>`
			},
			{
				"name": "Time Picker",
				"icon_html":  `<i aria-hidden='true' class='fa fa-clock-o'></i>`,
				"template_html":  `<tangible class='live-refresh'>\n<!-- Replace "my_time_field" with your custom field name --><field acf_time_picker='my_time_field'></field></tangible>`
			},
			{
				"name": "Repeater Starter Example ",
				"icon_html":  `<i aria-hidden='true' class='fa fa-table'></i>`,
				"template_html":  `
				<div class="lc-block">
					<tangible class="live-refresh">
						<!-- This code calls the repeater field 'faq' and displays subfields 'title' and 'content' -->
						<!-- Adjust code and fields to fit your needs -->
						<loop acf_repeater="therepeater">
							<h2>
								<field name="title"></field>
							</h2>
							<field name="content"></field>
						</loop>
					</tangible>
				</div>
				`
			},
			{
				"name": "Repeater to  Accordion",
				"icon_html":  `<i aria-hidden='true' class='fa fa-question-circle'></i>`,
				"template_html": `
						<tangible class="live-refresh"> 
						<!-- This code calls the repeater field 'faq' and displays subfields 'question' and 'answer' -->
						<!-- Adjust code and fields to fit your needs --> 
						<div class="accordion accordion-RANDOMID" id="accordion-RANDOMID">
							<loop acf_repeater="faq">
								<!--   ACCORDION ITEMS  -->
								<div class="accordion-item">
									<h2 class="accordion-header">
										<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#RANDOMID-collapse-{Get loop=count}" aria-expanded="false" aria-controls="RANDOMID-collapse-{Get loop=count}">
											<field name="question">
											</field>
										</button>
									</h2>
									<div id="RANDOMID-collapse-{Get loop=count}" class="accordion-collapse collapse" data-bs-parent="#accordion-RANDOMID">
										<div class="accordion-body">
											<field name="answer">
											</field>
										</div>
									</div>
								</div>
							</loop>
						</div>
					</tangible>
					`,
			},
			 
			{
				"name": "Repeater to Accordion (First Open)",
				"icon_html":  `<i aria-hidden='true' class='fa fa-question'></i>`,
				"template_html": `
					<tangible class="live-refresh"> 
						<!-- This code calls the repeater field 'faq' and displays subfields 'question' and 'answer' --> 
						<!-- Adjust code and fields to fit your needs --> 
						<div class="accordion accordion-flush" id="accordion-RANDOMID">
							<loop acf_repeater="faq">
								<if first="">
									<!-- FIRST ACCORDION ITEM: OPEN -->
									<div class="accordion-item">
										<h2 class="accordion-header">
											<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#RANDOMID-collapse-{Get loop=count}" aria-expanded="true" aria-controls="RANDOMID-collapse-{Get loop=count}">
												<field name="question">
												</field>
											</button>
										</h2>
										<div id="RANDOMID-collapse-{Get loop=count}" class="accordion-collapse collapse show" data-bs-parent="#accordion-RANDOMID">
											<div class="accordion-body">
												<field name="answer">
												</field>
											</div>
										</div>
									</div>
									<else>
										<!-- OTHER ACCORDION ITEMS: CLOSED -->
										<div class="accordion-item">
											<h2 class="accordion-header">
												<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#RANDOMID-collapse-{Get loop=count}" aria-expanded="false" aria-controls="RANDOMID-collapse-{Get loop=count}">
													<field name="question">
													</field>
												</button>
											</h2>
											<div id="RANDOMID-collapse-{Get loop=count}" class="accordion-collapse collapse" data-bs-parent="#accordion-RANDOMID">
												<div class="accordion-body">
													<field name="answer">
													</field>
												</div>
											</div>
										</div>
									</else>
								</if>
							</loop>
						</div>
					</tangible>
				`,
			},
 
			/* {
				"name": "Relationship",
				"icon_html":  `<i aria-hidden='true' class='fa fa-random'></i>`,
				"template_html":  `<tangible class='live-refresh'><loop acf_relationship='my_relationship_field'><a href='{Field url}'>{Field title}</a></loop></tangible>`
			}, */	
			/* {
				"name": "Flexible Content",
				"icon_html":  `<i aria-hidden='true' class='fa fa-columns'></i>`,
				"template_html":  `<tangible class='live-refresh'><loop acf_flexible_content='my_flexible_field'><field sub_field='sub_field_name'></field></loop></tangible>`
			}, */
			
		],
	},
	"WooCommerce Integration not tested": {
		description: "Shortcodes and helpers for WooCommerce content and listings.",
		blocks: [
			{
				name: "Products",
				icon_html: `<svg fill="current" height="18" viewbox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M229.334 58.6665H26.6673C14.886 58.6665 5.33398 68.2185 5.33398 79.9998V165.333C5.33398 177.114 14.886 186.666 26.6673 186.666H128.001L170.667 208L160.001 186.666H229.334C241.115 186.666 250.667 177.114 250.667 165.333V79.9998C250.667 68.2185 241.115 58.6665 229.334 58.6665Z" fill="white"></path><path d="M215.695 101.333C215.914 101.333 216.399 101.36 217.172 101.536C218.996 101.947 220.154 102.592 221.546 105.195C223.199 108.176 223.999 112.037 223.999 117.008C223.999 124.464 222.308 131.083 218.81 137.285C215.044 144 212.292 144 210.97 144C210.751 144 210.266 143.973 209.492 143.797C207.668 143.387 206.511 142.741 205.178 140.245C203.508 137.184 202.666 133.136 202.666 128.229C202.666 120.768 204.34 114.203 207.802 108.149C211.684 101.333 214.495 101.333 215.695 101.333ZM215.695 85.3335C206.788 85.3335 199.556 90.2988 193.898 100.229C189.076 108.667 186.666 118 186.666 128.229C186.666 135.877 188.148 142.427 191.119 147.888C194.458 154.144 199.375 157.915 205.962 159.408C207.722 159.803 209.391 160 210.97 160C219.967 160 227.199 155.035 232.767 145.104C237.588 136.565 239.999 127.232 239.999 117.008C239.999 109.264 238.516 102.811 235.546 97.4455C232.207 91.1895 227.29 87.4188 220.703 85.9255C218.943 85.5308 217.274 85.3335 215.695 85.3335ZM151.695 101.333C151.914 101.333 152.399 101.36 153.124 101.525C154.9 101.931 156.17 102.629 157.546 105.2C159.199 108.176 159.999 112.037 159.999 117.008C159.999 124.464 158.308 131.083 154.81 137.285C151.044 144 148.292 144 146.97 144C146.751 144 146.266 143.973 145.492 143.797C143.668 143.387 142.511 142.741 141.178 140.245C139.508 137.184 138.666 133.136 138.666 128.229C138.666 120.768 140.34 114.203 143.802 108.149C147.684 101.333 150.495 101.333 151.695 101.333ZM151.695 85.3335C142.788 85.3335 135.556 90.2988 129.898 100.229C125.076 108.667 122.666 118 122.666 128.229C122.666 135.877 124.148 142.427 127.119 147.888C130.458 154.144 135.375 157.915 141.962 159.408C143.722 159.803 145.391 160 146.97 160C155.967 160 163.199 155.035 168.767 145.104C173.588 136.565 175.999 127.232 175.999 117.008C175.999 109.264 174.516 102.811 171.546 97.4455C168.207 91.1895 163.199 87.4188 156.703 85.9255C154.943 85.5308 153.274 85.3335 151.695 85.3335ZM98.6658 170.667C96.4204 170.667 94.2284 169.717 92.6764 167.973C80.9644 154.752 73.7698 137.387 69.5298 123.477C63.4284 136.384 55.1938 152.891 46.8578 166.779C45.2098 169.525 42.0898 171.003 38.9058 170.592C35.7271 170.149 33.1191 167.856 32.2818 164.763C20.4844 121.419 16.2444 90.3628 16.0738 89.0615C15.4871 84.6881 18.5591 80.6615 22.9324 80.0695C27.3644 79.4935 31.3378 82.5495 31.9298 86.9282C31.9671 87.1895 34.9751 109.205 42.7724 141.323C54.6018 118.88 64.5218 95.8028 64.6498 95.5095C66.0738 92.2028 69.5511 90.2348 73.0764 90.7362C76.6391 91.2162 79.4444 94.0161 79.9298 97.5788C79.9671 97.8561 82.8204 117.979 91.9031 137.317C95.7378 99.0988 107.226 79.5095 107.807 78.5388C110.084 74.7468 115.007 73.5202 118.783 75.7922C122.575 78.0642 123.802 82.9815 121.53 86.7681C121.396 87.0028 106.666 112.613 106.666 162.667C106.666 165.989 104.607 168.971 101.503 170.144C100.575 170.496 99.6204 170.667 98.6658 170.667Z" fill="var(--color-accents)"></path></svg>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[products limit="12" columns="3" category=""] </div>`
			},
			{
				name: "All Categories",
				icon_html: `<svg fill="current" height="18" viewbox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M229.334 58.6665H26.6673C14.886 58.6665 5.33398 68.2185 5.33398 79.9998V165.333C5.33398 177.114 14.886 186.666 26.6673 186.666H128.001L170.667 208L160.001 186.666H229.334C241.115 186.666 250.667 177.114 250.667 165.333V79.9998C250.667 68.2185 241.115 58.6665 229.334 58.6665Z" fill="white"></path><path d="M215.695 101.333C215.914 101.333 216.399 101.36 217.172 101.536C218.996 101.947 220.154 102.592 221.546 105.195C223.199 108.176 223.999 112.037 223.999 117.008C223.999 124.464 222.308 131.083 218.81 137.285C215.044 144 212.292 144 210.97 144C210.751 144 210.266 143.973 209.492 143.797C207.668 143.387 206.511 142.741 205.178 140.245C203.508 137.184 202.666 133.136 202.666 128.229C202.666 120.768 204.34 114.203 207.802 108.149C211.684 101.333 214.495 101.333 215.695 101.333ZM215.695 85.3335C206.788 85.3335 199.556 90.2988 193.898 100.229C189.076 108.667 186.666 118 186.666 128.229C186.666 135.877 188.148 142.427 191.119 147.888C194.458 154.144 199.375 157.915 205.962 159.408C207.722 159.803 209.391 160 210.97 160C219.967 160 227.199 155.035 232.767 145.104C237.588 136.565 239.999 127.232 239.999 117.008C239.999 109.264 238.516 102.811 235.546 97.4455C232.207 91.1895 227.29 87.4188 220.703 85.9255C218.943 85.5308 217.274 85.3335 215.695 85.3335ZM151.695 101.333C151.914 101.333 152.399 101.36 153.124 101.525C154.9 101.931 156.17 102.629 157.546 105.2C159.199 108.176 159.999 112.037 159.999 117.008C159.999 124.464 158.308 131.083 154.81 137.285C151.044 144 148.292 144 146.97 144C146.751 144 146.266 143.973 145.492 143.797C143.668 143.387 142.511 142.741 141.178 140.245C139.508 137.184 138.666 133.136 138.666 128.229C138.666 120.768 140.34 114.203 143.802 108.149C147.684 101.333 150.495 101.333 151.695 101.333ZM151.695 85.3335C142.788 85.3335 135.556 90.2988 129.898 100.229C125.076 108.667 122.666 118 122.666 128.229C122.666 135.877 124.148 142.427 127.119 147.888C130.458 154.144 135.375 157.915 141.962 159.408C143.722 159.803 145.391 160 146.97 160C155.967 160 163.199 155.035 168.767 145.104C173.588 136.565 175.999 127.232 175.999 117.008C175.999 109.264 174.516 102.811 171.546 97.4455C168.207 91.1895 163.199 87.4188 156.703 85.9255C154.943 85.5308 153.274 85.3335 151.695 85.3335ZM98.6658 170.667C96.4204 170.667 94.2284 169.717 92.6764 167.973C80.9644 154.752 73.7698 137.387 69.5298 123.477C63.4284 136.384 55.1938 152.891 46.8578 166.779C45.2098 169.525 42.0898 171.003 38.9058 170.592C35.7271 170.149 33.1191 167.856 32.2818 164.763C20.4844 121.419 16.2444 90.3628 16.0738 89.0615C15.4871 84.6881 18.5591 80.6615 22.9324 80.0695C27.3644 79.4935 31.3378 82.5495 31.9298 86.9282C31.9671 87.1895 34.9751 109.205 42.7724 141.323C54.6018 118.88 64.5218 95.8028 64.6498 95.5095C66.0738 92.2028 69.5511 90.2348 73.0764 90.7362C76.6391 91.2162 79.4444 94.0161 79.9298 97.5788C79.9671 97.8561 82.8204 117.979 91.9031 137.317C95.7378 99.0988 107.226 79.5095 107.807 78.5388C110.084 74.7468 115.007 73.5202 118.783 75.7922C122.575 78.0642 123.802 82.9815 121.53 86.7681C121.396 87.0028 106.666 112.613 106.666 162.667C106.666 165.989 104.607 168.971 101.503 170.144C100.575 170.496 99.6204 170.667 98.6658 170.667Z" fill="var(--color-accents)"></path></svg>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[product_categories columns="3"]</div>`
			},
			{
				name: "Top Level Categories",
				icon_html: `<svg fill="current" height="18" viewbox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M229.334 58.6665H26.6673C14.886 58.6665 5.33398 68.2185 5.33398 79.9998V165.333C5.33398 177.114 14.886 186.666 26.6673 186.666H128.001L170.667 208L160.001 186.666H229.334C241.115 186.666 250.667 177.114 250.667 165.333V79.9998C250.667 68.2185 241.115 58.6665 229.334 58.6665Z" fill="white"></path><path d="M215.695 101.333C215.914 101.333 216.399 101.36 217.172 101.536C218.996 101.947 220.154 102.592 221.546 105.195C223.199 108.176 223.999 112.037 223.999 117.008C223.999 124.464 222.308 131.083 218.81 137.285C215.044 144 212.292 144 210.97 144C210.751 144 210.266 143.973 209.492 143.797C207.668 143.387 206.511 142.741 205.178 140.245C203.508 137.184 202.666 133.136 202.666 128.229C202.666 120.768 204.34 114.203 207.802 108.149C211.684 101.333 214.495 101.333 215.695 101.333ZM215.695 85.3335C206.788 85.3335 199.556 90.2988 193.898 100.229C189.076 108.667 186.666 118 186.666 128.229C186.666 135.877 188.148 142.427 191.119 147.888C194.458 154.144 199.375 157.915 205.962 159.408C207.722 159.803 209.391 160 210.97 160C219.967 160 227.199 155.035 232.767 145.104C237.588 136.565 239.999 127.232 239.999 117.008C239.999 109.264 238.516 102.811 235.546 97.4455C232.207 91.1895 227.29 87.4188 220.703 85.9255C218.943 85.5308 217.274 85.3335 215.695 85.3335ZM151.695 101.333C151.914 101.333 152.399 101.36 153.124 101.525C154.9 101.931 156.17 102.629 157.546 105.2C159.199 108.176 159.999 112.037 159.999 117.008C159.999 124.464 158.308 131.083 154.81 137.285C151.044 144 148.292 144 146.97 144C146.751 144 146.266 143.973 145.492 143.797C143.668 143.387 142.511 142.741 141.178 140.245C139.508 137.184 138.666 133.136 138.666 128.229C138.666 120.768 140.34 114.203 143.802 108.149C147.684 101.333 150.495 101.333 151.695 101.333ZM151.695 85.3335C142.788 85.3335 135.556 90.2988 129.898 100.229C125.076 108.667 122.666 118 122.666 128.229C122.666 135.877 124.148 142.427 127.119 147.888C130.458 154.144 135.375 157.915 141.962 159.408C143.722 159.803 145.391 160 146.97 160C155.967 160 163.199 155.035 168.767 145.104C173.588 136.565 175.999 127.232 175.999 117.008C175.999 109.264 174.516 102.811 171.546 97.4455C168.207 91.1895 163.199 87.4188 156.703 85.9255C154.943 85.5308 153.274 85.3335 151.695 85.3335ZM98.6658 170.667C96.4204 170.667 94.2284 169.717 92.6764 167.973C80.9644 154.752 73.7698 137.387 69.5298 123.477C63.4284 136.384 55.1938 152.891 46.8578 166.779C45.2098 169.525 42.0898 171.003 38.9058 170.592C35.7271 170.149 33.1191 167.856 32.2818 164.763C20.4844 121.419 16.2444 90.3628 16.0738 89.0615C15.4871 84.6881 18.5591 80.6615 22.9324 80.0695C27.3644 79.4935 31.3378 82.5495 31.9298 86.9282C31.9671 87.1895 34.9751 109.205 42.7724 141.323C54.6018 118.88 64.5218 95.8028 64.6498 95.5095C66.0738 92.2028 69.5511 90.2348 73.0764 90.7362C76.6391 91.2162 79.4444 94.0161 79.9298 97.5788C79.9671 97.8561 82.8204 117.979 91.9031 137.317C95.7378 99.0988 107.226 79.5095 107.807 78.5388C110.084 74.7468 115.007 73.5202 118.783 75.7922C122.575 78.0642 123.802 82.9815 121.53 86.7681C121.396 87.0028 106.666 112.613 106.666 162.667C106.666 165.989 104.607 168.971 101.503 170.144C100.575 170.496 99.6204 170.667 98.6658 170.667Z" fill="var(--color-accents)"></path></svg>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[product_categories columns="3" number="0" parent="0"]</div>`
			},
			{
				name: "Recent Products",
				icon_html: `<svg fill="current" height="18" viewbox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M229.334 58.6665H26.6673C14.886 58.6665 5.33398 68.2185 5.33398 79.9998V165.333C5.33398 177.114 14.886 186.666 26.6673 186.666H128.001L170.667 208L160.001 186.666H229.334C241.115 186.666 250.667 177.114 250.667 165.333V79.9998C250.667 68.2185 241.115 58.6665 229.334 58.6665Z" fill="white"></path><path d="M215.695 101.333C215.914 101.333 216.399 101.36 217.172 101.536C218.996 101.947 220.154 102.592 221.546 105.195C223.199 108.176 223.999 112.037 223.999 117.008C223.999 124.464 222.308 131.083 218.81 137.285C215.044 144 212.292 144 210.97 144C210.751 144 210.266 143.973 209.492 143.797C207.668 143.387 206.511 142.741 205.178 140.245C203.508 137.184 202.666 133.136 202.666 128.229C202.666 120.768 204.34 114.203 207.802 108.149C211.684 101.333 214.495 101.333 215.695 101.333ZM215.695 85.3335C206.788 85.3335 199.556 90.2988 193.898 100.229C189.076 108.667 186.666 118 186.666 128.229C186.666 135.877 188.148 142.427 191.119 147.888C194.458 154.144 199.375 157.915 205.962 159.408C207.722 159.803 209.391 160 210.97 160C219.967 160 227.199 155.035 232.767 145.104C237.588 136.565 239.999 127.232 239.999 117.008C239.999 109.264 238.516 102.811 235.546 97.4455C232.207 91.1895 227.29 87.4188 220.703 85.9255C218.943 85.5308 217.274 85.3335 215.695 85.3335ZM151.695 101.333C151.914 101.333 152.399 101.36 153.124 101.525C154.9 101.931 156.17 102.629 157.546 105.2C159.199 108.176 159.999 112.037 159.999 117.008C159.999 124.464 158.308 131.083 154.81 137.285C151.044 144 148.292 144 146.97 144C146.751 144 146.266 143.973 145.492 143.797C143.668 143.387 142.511 142.741 141.178 140.245C139.508 137.184 138.666 133.136 138.666 128.229C138.666 120.768 140.34 114.203 143.802 108.149C147.684 101.333 150.495 101.333 151.695 101.333ZM151.695 85.3335C142.788 85.3335 135.556 90.2988 129.898 100.229C125.076 108.667 122.666 118 122.666 128.229C122.666 135.877 124.148 142.427 127.119 147.888C130.458 154.144 135.375 157.915 141.962 159.408C143.722 159.803 145.391 160 146.97 160C155.967 160 163.199 155.035 168.767 145.104C173.588 136.565 175.999 127.232 175.999 117.008C175.999 109.264 174.516 102.811 171.546 97.4455C168.207 91.1895 163.199 87.4188 156.703 85.9255C154.943 85.5308 153.274 85.3335 151.695 85.3335ZM98.6658 170.667C96.4204 170.667 94.2284 169.717 92.6764 167.973C80.9644 154.752 73.7698 137.387 69.5298 123.477C63.4284 136.384 55.1938 152.891 46.8578 166.779C45.2098 169.525 42.0898 171.003 38.9058 170.592C35.7271 170.149 33.1191 167.856 32.2818 164.763C20.4844 121.419 16.2444 90.3628 16.0738 89.0615C15.4871 84.6881 18.5591 80.6615 22.9324 80.0695C27.3644 79.4935 31.3378 82.5495 31.9298 86.9282C31.9671 87.1895 34.9751 109.205 42.7724 141.323C54.6018 118.88 64.5218 95.8028 64.6498 95.5095C66.0738 92.2028 69.5511 90.2348 73.0764 90.7362C76.6391 91.2162 79.4444 94.0161 79.9298 97.5788C79.9671 97.8561 82.8204 117.979 91.9031 137.317C95.7378 99.0988 107.226 79.5095 107.807 78.5388C110.084 74.7468 115.007 73.5202 118.783 75.7922C122.575 78.0642 123.802 82.9815 121.53 86.7681C121.396 87.0028 106.666 112.613 106.666 162.667C106.666 165.989 104.607 168.971 101.503 170.144C100.575 170.496 99.6204 170.667 98.6658 170.667Z" fill="var(--color-accents)"></path></svg>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[recent_products per_page="12" columns="3"]</div>`
			},
			{
				name: "Featured Products",
				icon_html: `<svg fill="current" height="18" viewbox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M229.334 58.6665H26.6673C14.886 58.6665 5.33398 68.2185 5.33398 79.9998V165.333C5.33398 177.114 14.886 186.666 26.6673 186.666H128.001L170.667 208L160.001 186.666H229.334C241.115 186.666 250.667 177.114 250.667 165.333V79.9998C250.667 68.2185 241.115 58.6665 229.334 58.6665Z" fill="white"></path><path d="M215.695 101.333C215.914 101.333 216.399 101.36 217.172 101.536C218.996 101.947 220.154 102.592 221.546 105.195C223.199 108.176 223.999 112.037 223.999 117.008C223.999 124.464 222.308 131.083 218.81 137.285C215.044 144 212.292 144 210.97 144C210.751 144 210.266 143.973 209.492 143.797C207.668 143.387 206.511 142.741 205.178 140.245C203.508 137.184 202.666 133.136 202.666 128.229C202.666 120.768 204.34 114.203 207.802 108.149C211.684 101.333 214.495 101.333 215.695 101.333ZM215.695 85.3335C206.788 85.3335 199.556 90.2988 193.898 100.229C189.076 108.667 186.666 118 186.666 128.229C186.666 135.877 188.148 142.427 191.119 147.888C194.458 154.144 199.375 157.915 205.962 159.408C207.722 159.803 209.391 160 210.97 160C219.967 160 227.199 155.035 232.767 145.104C237.588 136.565 239.999 127.232 239.999 117.008C239.999 109.264 238.516 102.811 235.546 97.4455C232.207 91.1895 227.29 87.4188 220.703 85.9255C218.943 85.5308 217.274 85.3335 215.695 85.3335ZM151.695 101.333C151.914 101.333 152.399 101.36 153.124 101.525C154.9 101.931 156.17 102.629 157.546 105.2C159.199 108.176 159.999 112.037 159.999 117.008C159.999 124.464 158.308 131.083 154.81 137.285C151.044 144 148.292 144 146.97 144C146.751 144 146.266 143.973 145.492 143.797C143.668 143.387 142.511 142.741 141.178 140.245C139.508 137.184 138.666 133.136 138.666 128.229C138.666 120.768 140.34 114.203 143.802 108.149C147.684 101.333 150.495 101.333 151.695 101.333ZM151.695 85.3335C142.788 85.3335 135.556 90.2988 129.898 100.229C125.076 108.667 122.666 118 122.666 128.229C122.666 135.877 124.148 142.427 127.119 147.888C130.458 154.144 135.375 157.915 141.962 159.408C143.722 159.803 145.391 160 146.97 160C155.967 160 163.199 155.035 168.767 145.104C173.588 136.565 175.999 127.232 175.999 117.008C175.999 109.264 174.516 102.811 171.546 97.4455C168.207 91.1895 163.199 87.4188 156.703 85.9255C154.943 85.5308 153.274 85.3335 151.695 85.3335ZM98.6658 170.667C96.4204 170.667 94.2284 169.717 92.6764 167.973C80.9644 154.752 73.7698 137.387 69.5298 123.477C63.4284 136.384 55.1938 152.891 46.8578 166.779C45.2098 169.525 42.0898 171.003 38.9058 170.592C35.7271 170.149 33.1191 167.856 32.2818 164.763C20.4844 121.419 16.2444 90.3628 16.0738 89.0615C15.4871 84.6881 18.5591 80.6615 22.9324 80.0695C27.3644 79.4935 31.3378 82.5495 31.9298 86.9282C31.9671 87.1895 34.9751 109.205 42.7724 141.323C54.6018 118.88 64.5218 95.8028 64.6498 95.5095C66.0738 92.2028 69.5511 90.2348 73.0764 90.7362C76.6391 91.2162 79.4444 94.0161 79.9298 97.5788C79.9671 97.8561 82.8204 117.979 91.9031 137.317C95.7378 99.0988 107.226 79.5095 107.807 78.5388C110.084 74.7468 115.007 73.5202 118.783 75.7922C122.575 78.0642 123.802 82.9815 121.53 86.7681C121.396 87.0028 106.666 112.613 106.666 162.667C106.666 165.989 104.607 168.971 101.503 170.144C100.575 170.496 99.6204 170.667 98.6658 170.667Z" fill="var(--color-accents)"></path></svg>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[featured_products per_page="12" columns="3"] </div>`
			},
			{
				name: "Sale Products",
				icon_html: `<svg fill="current" height="18" viewbox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M229.334 58.6665H26.6673C14.886 58.6665 5.33398 68.2185 5.33398 79.9998V165.333C5.33398 177.114 14.886 186.666 26.6673 186.666H128.001L170.667 208L160.001 186.666H229.334C241.115 186.666 250.667 177.114 250.667 165.333V79.9998C250.667 68.2185 241.115 58.6665 229.334 58.6665Z" fill="white"></path><path d="M215.695 101.333C215.914 101.333 216.399 101.36 217.172 101.536C218.996 101.947 220.154 102.592 221.546 105.195C223.199 108.176 223.999 112.037 223.999 117.008C223.999 124.464 222.308 131.083 218.81 137.285C215.044 144 212.292 144 210.97 144C210.751 144 210.266 143.973 209.492 143.797C207.668 143.387 206.511 142.741 205.178 140.245C203.508 137.184 202.666 133.136 202.666 128.229C202.666 120.768 204.34 114.203 207.802 108.149C211.684 101.333 214.495 101.333 215.695 101.333ZM215.695 85.3335C206.788 85.3335 199.556 90.2988 193.898 100.229C189.076 108.667 186.666 118 186.666 128.229C186.666 135.877 188.148 142.427 191.119 147.888C194.458 154.144 199.375 157.915 205.962 159.408C207.722 159.803 209.391 160 210.97 160C219.967 160 227.199 155.035 232.767 145.104C237.588 136.565 239.999 127.232 239.999 117.008C239.999 109.264 238.516 102.811 235.546 97.4455C232.207 91.1895 227.29 87.4188 220.703 85.9255C218.943 85.5308 217.274 85.3335 215.695 85.3335ZM151.695 101.333C151.914 101.333 152.399 101.36 153.124 101.525C154.9 101.931 156.17 102.629 157.546 105.2C159.199 108.176 159.999 112.037 159.999 117.008C159.999 124.464 158.308 131.083 154.81 137.285C151.044 144 148.292 144 146.97 144C146.751 144 146.266 143.973 145.492 143.797C143.668 143.387 142.511 142.741 141.178 140.245C139.508 137.184 138.666 133.136 138.666 128.229C138.666 120.768 140.34 114.203 143.802 108.149C147.684 101.333 150.495 101.333 151.695 101.333ZM151.695 85.3335C142.788 85.3335 135.556 90.2988 129.898 100.229C125.076 108.667 122.666 118 122.666 128.229C122.666 135.877 124.148 142.427 127.119 147.888C130.458 154.144 135.375 157.915 141.962 159.408C143.722 159.803 145.391 160 146.97 160C155.967 160 163.199 155.035 168.767 145.104C173.588 136.565 175.999 127.232 175.999 117.008C175.999 109.264 174.516 102.811 171.546 97.4455C168.207 91.1895 163.199 87.4188 156.703 85.9255C154.943 85.5308 153.274 85.3335 151.695 85.3335ZM98.6658 170.667C96.4204 170.667 94.2284 169.717 92.6764 167.973C80.9644 154.752 73.7698 137.387 69.5298 123.477C63.4284 136.384 55.1938 152.891 46.8578 166.779C45.2098 169.525 42.0898 171.003 38.9058 170.592C35.7271 170.149 33.1191 167.856 32.2818 164.763C20.4844 121.419 16.2444 90.3628 16.0738 89.0615C15.4871 84.6881 18.5591 80.6615 22.9324 80.0695C27.3644 79.4935 31.3378 82.5495 31.9298 86.9282C31.9671 87.1895 34.9751 109.205 42.7724 141.323C54.6018 118.88 64.5218 95.8028 64.6498 95.5095C66.0738 92.2028 69.5511 90.2348 73.0764 90.7362C76.6391 91.2162 79.4444 94.0161 79.9298 97.5788C79.9671 97.8561 82.8204 117.979 91.9031 137.317C95.7378 99.0988 107.226 79.5095 107.807 78.5388C110.084 74.7468 115.007 73.5202 118.783 75.7922C122.575 78.0642 123.802 82.9815 121.53 86.7681C121.396 87.0028 106.666 112.613 106.666 162.667C106.666 165.989 104.607 168.971 101.503 170.144C100.575 170.496 99.6204 170.667 98.6658 170.667Z" fill="var(--color-accents)"></path></svg>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[sale_products per_page="12" columns="3"]</div>`
			},
			{
				name: "Top Rated Products",
				icon_html: `<svg fill="current" height="18" viewbox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M229.334 58.6665H26.6673C14.886 58.6665 5.33398 68.2185 5.33398 79.9998V165.333C5.33398 177.114 14.886 186.666 26.6673 186.666H128.001L170.667 208L160.001 186.666H229.334C241.115 186.666 250.667 177.114 250.667 165.333V79.9998C250.667 68.2185 241.115 58.6665 229.334 58.6665Z" fill="white"></path><path d="M215.695 101.333C215.914 101.333 216.399 101.36 217.172 101.536C218.996 101.947 220.154 102.592 221.546 105.195C223.199 108.176 223.999 112.037 223.999 117.008C223.999 124.464 222.308 131.083 218.81 137.285C215.044 144 212.292 144 210.97 144C210.751 144 210.266 143.973 209.492 143.797C207.668 143.387 206.511 142.741 205.178 140.245C203.508 137.184 202.666 133.136 202.666 128.229C202.666 120.768 204.34 114.203 207.802 108.149C211.684 101.333 214.495 101.333 215.695 101.333ZM215.695 85.3335C206.788 85.3335 199.556 90.2988 193.898 100.229C189.076 108.667 186.666 118 186.666 128.229C186.666 135.877 188.148 142.427 191.119 147.888C194.458 154.144 199.375 157.915 205.962 159.408C207.722 159.803 209.391 160 210.97 160C219.967 160 227.199 155.035 232.767 145.104C237.588 136.565 239.999 127.232 239.999 117.008C239.999 109.264 238.516 102.811 235.546 97.4455C232.207 91.1895 227.29 87.4188 220.703 85.9255C218.943 85.5308 217.274 85.3335 215.695 85.3335ZM151.695 101.333C151.914 101.333 152.399 101.36 153.124 101.525C154.9 101.931 156.17 102.629 157.546 105.2C159.199 108.176 159.999 112.037 159.999 117.008C159.999 124.464 158.308 131.083 154.81 137.285C151.044 144 148.292 144 146.97 144C146.751 144 146.266 143.973 145.492 143.797C143.668 143.387 142.511 142.741 141.178 140.245C139.508 137.184 138.666 133.136 138.666 128.229C138.666 120.768 140.34 114.203 143.802 108.149C147.684 101.333 150.495 101.333 151.695 101.333ZM151.695 85.3335C142.788 85.3335 135.556 90.2988 129.898 100.229C125.076 108.667 122.666 118 122.666 128.229C122.666 135.877 124.148 142.427 127.119 147.888C130.458 154.144 135.375 157.915 141.962 159.408C143.722 159.803 145.391 160 146.97 160C155.967 160 163.199 155.035 168.767 145.104C173.588 136.565 175.999 127.232 175.999 117.008C175.999 109.264 174.516 102.811 171.546 97.4455C168.207 91.1895 163.199 87.4188 156.703 85.9255C154.943 85.5308 153.274 85.3335 151.695 85.3335ZM98.6658 170.667C96.4204 170.667 94.2284 169.717 92.6764 167.973C80.9644 154.752 73.7698 137.387 69.5298 123.477C63.4284 136.384 55.1938 152.891 46.8578 166.779C45.2098 169.525 42.0898 171.003 38.9058 170.592C35.7271 170.149 33.1191 167.856 32.2818 164.763C20.4844 121.419 16.2444 90.3628 16.0738 89.0615C15.4871 84.6881 18.5591 80.6615 22.9324 80.0695C27.3644 79.4935 31.3378 82.5495 31.9298 86.9282C31.9671 87.1895 34.9751 109.205 42.7724 141.323C54.6018 118.88 64.5218 95.8028 64.6498 95.5095C66.0738 92.2028 69.5511 90.2348 73.0764 90.7362C76.6391 91.2162 79.4444 94.0161 79.9298 97.5788C79.9671 97.8561 82.8204 117.979 91.9031 137.317C95.7378 99.0988 107.226 79.5095 107.807 78.5388C110.084 74.7468 115.007 73.5202 118.783 75.7922C122.575 78.0642 123.802 82.9815 121.53 86.7681C121.396 87.0028 106.666 112.613 106.666 162.667C106.666 165.989 104.607 168.971 101.503 170.144C100.575 170.496 99.6204 170.667 98.6658 170.667Z" fill="var(--color-accents)"></path></svg>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[top_rated_products per_page="12" columns="3"] </div>`
			},
			{
				name: "Best Selling Products",
				icon_html: `<svg fill="current" height="18" viewbox="0 0 256 256" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M229.334 58.6665H26.6673C14.886 58.6665 5.33398 68.2185 5.33398 79.9998V165.333C5.33398 177.114 14.886 186.666 26.6673 186.666H128.001L170.667 208L160.001 186.666H229.334C241.115 186.666 250.667 177.114 250.667 165.333V79.9998C250.667 68.2185 241.115 58.6665 229.334 58.6665Z" fill="white"></path><path d="M215.695 101.333C215.914 101.333 216.399 101.36 217.172 101.536C218.996 101.947 220.154 102.592 221.546 105.195C223.199 108.176 223.999 112.037 223.999 117.008C223.999 124.464 222.308 131.083 218.81 137.285C215.044 144 212.292 144 210.97 144C210.751 144 210.266 143.973 209.492 143.797C207.668 143.387 206.511 142.741 205.178 140.245C203.508 137.184 202.666 133.136 202.666 128.229C202.666 120.768 204.34 114.203 207.802 108.149C211.684 101.333 214.495 101.333 215.695 101.333ZM215.695 85.3335C206.788 85.3335 199.556 90.2988 193.898 100.229C189.076 108.667 186.666 118 186.666 128.229C186.666 135.877 188.148 142.427 191.119 147.888C194.458 154.144 199.375 157.915 205.962 159.408C207.722 159.803 209.391 160 210.97 160C219.967 160 227.199 155.035 232.767 145.104C237.588 136.565 239.999 127.232 239.999 117.008C239.999 109.264 238.516 102.811 235.546 97.4455C232.207 91.1895 227.29 87.4188 220.703 85.9255C218.943 85.5308 217.274 85.3335 215.695 85.3335ZM151.695 101.333C151.914 101.333 152.399 101.36 153.124 101.525C154.9 101.931 156.17 102.629 157.546 105.2C159.199 108.176 159.999 112.037 159.999 117.008C159.999 124.464 158.308 131.083 154.81 137.285C151.044 144 148.292 144 146.97 144C146.751 144 146.266 143.973 145.492 143.797C143.668 143.387 142.511 142.741 141.178 140.245C139.508 137.184 138.666 133.136 138.666 128.229C138.666 120.768 140.34 114.203 143.802 108.149C147.684 101.333 150.495 101.333 151.695 101.333ZM151.695 85.3335C142.788 85.3335 135.556 90.2988 129.898 100.229C125.076 108.667 122.666 118 122.666 128.229C122.666 135.877 124.148 142.427 127.119 147.888C130.458 154.144 135.375 157.915 141.962 159.408C143.722 159.803 145.391 160 146.97 160C155.967 160 163.199 155.035 168.767 145.104C173.588 136.565 175.999 127.232 175.999 117.008C175.999 109.264 174.516 102.811 171.546 97.4455C168.207 91.1895 163.199 87.4188 156.703 85.9255C154.943 85.5308 153.274 85.3335 151.695 85.3335ZM98.6658 170.667C96.4204 170.667 94.2284 169.717 92.6764 167.973C80.9644 154.752 73.7698 137.387 69.5298 123.477C63.4284 136.384 55.1938 152.891 46.8578 166.779C45.2098 169.525 42.0898 171.003 38.9058 170.592C35.7271 170.149 33.1191 167.856 32.2818 164.763C20.4844 121.419 16.2444 90.3628 16.0738 89.0615C15.4871 84.6881 18.5591 80.6615 22.9324 80.0695C27.3644 79.4935 31.3378 82.5495 31.9298 86.9282C31.9671 87.1895 34.9751 109.205 42.7724 141.323C54.6018 118.88 64.5218 95.8028 64.6498 95.5095C66.0738 92.2028 69.5511 90.2348 73.0764 90.7362C76.6391 91.2162 79.4444 94.0161 79.9298 97.5788C79.9671 97.8561 82.8204 117.979 91.9031 137.317C95.7378 99.0988 107.226 79.5095 107.807 78.5388C110.084 74.7468 115.007 73.5202 118.783 75.7922C122.575 78.0642 123.802 82.9815 121.53 86.7681C121.396 87.0028 106.666 112.613 106.666 162.667C106.666 165.989 104.607 168.971 101.503 170.144C100.575 170.496 99.6204 170.667 98.6658 170.667Z" fill="var(--color-accents)"></path></svg>`,
				template_html: `<div class="live-shortcode" lc-helper="shortcode">[best_selling_products per_page="12" columns="3"] </div>`
			},
			{
				name: "WooCommerce Blocks: Mini Cart",
				icon_html: `<i aria-hidden="true" class="fa fa-shopping-basket"></i>`,
				template_html: `[lc_wc_block name="mini-cart"]`
			},
		],
	},
	"WordPress Templating not tested": {
		description: "Templating snippets for building classic WordPress theme views.",
		blocks: [
			{
				name: "Post Title",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<!-- the title --><h1 class="text-4xl font-bold"><field name="title"></field></h1>`
			},
			{
				name: "Post Content",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<!-- content --><field name="content"></field>`
			},
			{
				name: "Post Edit Link",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<if includes="" user_role="" value="administrator, editor, author"><a class="link link-hover block mt-4 mb-5" href="{Field edit_url}">Edit this post</a></if>`
			},
			{
				name: "Post ID",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<field name="id"></field>`
			},
			{
				name: "Post Date",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<field date_format="F j, Y" name="publish_date"></field>`
			},
			{
				name: "Author Fields",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<field name="author_full_name"></field>`
			},
			{
				name: "Author Avatar",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<!-- Author Avatar --><if field="author_avatar"><img alt="{Field author_full_name}" class="rounded-full shadow border-2 border-base-300" height="72" src="{Field author_avatar_url size='72'}" width="72"/></if>`
			},
			{
				name: "Author Posts URL",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<a class="link link-hover inline-flex items-center gap-2" href="{Field author_archive_url}"> Read all my posts <svg class="h-4 w-4" fill="currentColor" viewbox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" fill-rule="evenodd"></path></svg></a>`
			},
			{
				name: "Link to Post URL",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<a class="link link-hover" href="{Field url}"><field name="title"></field></a>`
			},
			{
				name: "Post Categories",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<!-- Post Categories --><loop post="current" taxonomy="category" type="taxonomy_term"><a class="badge badge-primary rounded-full no-underline" href="{Field url}"><field name="title"></field></a></loop>`
			},
			{
				name: "Post Tags",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<!-- Post Tags --><loop post="current" taxonomy="tag" type="taxonomy_term"><a class="badge badge-primary rounded-full no-underline" href="{Field url}"><field name="title"></field></a></loop>`
			},
			{
				name: "Custom Field Value",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<field name="mycustomfield"></field>`
			},
			{
				name: "ACF Repeater",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<loop acf_repeater="therepeater"><h2><field name="title"></field></h2><p><field name="content"></field></p></loop>`
			},
			{
				name: "Featured Image",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<if field="image"><img alt="{Field image_alt}" class="w-full" sizes="{Field image_sizes}" src="{Field image_url size='medium'}" srcset="{Field image_srcset}" style="height: 256px; object-fit: cover"/><else><img alt="Placeholder image" class="w-full" src="https://placehold.co/600x400?text=Placeholder" style="height: 256px; object-fit: cover"/></else></if>`
			},
			{
				name: "Simple Featured Image w/URL",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<img alt="{Field image_alt}" src="{Field image_url}"/>`
			},
			{
				name: "Post Sharing Buttons",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `[lc_the_sharing]`
			},
			{
				name: "Comments Form",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<template name="comments" theme="part"></template>`
			},
			{
				name: "Sidebar Widget Area",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `[lc_widgetsarea]`
			},
			{
				name: "Sample Post Loop for Archives",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<!-- Starts the loop for posts --><loop><div class="mb-4"><div class="card shadow h-full"><!-- Thumbnail --><div class="lc-block relative"><if field="image"><img alt="{Field image_alt}" class="w-full" sizes="{Field image_sizes}" src="{Field image_url size='medium'}" srcset="{Field image_srcset}" style="height: 256px; object-fit: cover"/><else><img alt="Placeholder image" class="w-full" src="https://placehold.co/600x400?text=Placeholder" style="height: 256px; object-fit: cover"/></else></if><div class="absolute top-2 right-2 text-right opacity-75"><!-- Post Categories --><loop post="current" taxonomy="category" type="taxonomy_term"><a class="badge badge-primary rounded-full no-underline" href="{Field url}"><field name="title"></field></a></loop></div></div><div class="card-body"><div class="lc-block"><!-- Post Date --><small class="text-base-content/60"><field date_format="F j, Y" name="publish_date"></field></small></div><div class="lc-block"><!-- Post Title --><h2 class="text-2xl font-bold"><a class="link link-hover" href="{Field url}"><field name="title"></field></a></h2></div><div class="lc-block mb-2"><!-- Post Excerpt --><field auto="true" more="..." name="excerpt" words="30"></field></div></div></div></div></loop><!-- End of the loop -->`
			},
			{
				name: "Excerpt",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<!-- Post Excerpt --><field auto="true" more="..." name="excerpt" words="30"></field>`
			},
			{
				name: "Sample Loop for Current Category Title and Description",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<loop taxonomy="category" terms="current" type="taxonomy_term"><div class="container mx-auto"><div class="lc-block"><!-- Archive Title --><h1 class="text-4xl font-bold"><field name="title"></field></h1></div><div class="lc-block max-w-2xl mx-auto mb-4"><!-- Archive Description --><div class="text-lg text-base-content/70"><field name="description"></field></div></div></div></loop>`
			},
			{
				name: "Order By Widget",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `[lc_the_orderby_widget]`
			},
			{
				name: "Pagination",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `[lc_the_pagination]`
			},
			{
				name: "Related Posts: Simple",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<div class="lc-block mx-auto max-w-3xl"><h2 class="font-bold text-2xl mb-3" editable="inline">You may also be interested in:</h2><!-- related posts --><loop category="current" count="10" exclude="{Field id}" order="desc" orderby="date" posts="current" taxonomy="category" type="post"><div class="card mb-3"><div class="grid md:grid-cols-3"><div><if field="image"><img class="w-full h-full object-cover rounded-t-box md:rounded-l-box md:rounded-tr-none" src="{Field image_url size='post-full'}"/><else><img class="w-full h-full object-cover rounded-t-box md:rounded-l-box md:rounded-tr-none" src="https://placehold.co/600x400?text=Placeholder"/></else></if></div><div class="md:col-span-2"><div class="card-body"><a class="link link-hover" href="{Field url}"><field name="title"></field></a><field auto="true" name="excerpt" words="30"></field></div></div></div></div><if previous_total="" value="0"><p>No related posts found.</p></if></loop></div>`
			},
			{
				name: "Related Posts: Card",
				icon_html: `<i aria-hidden="true" class="fa fa-wordpress"></i>`,
				template_html: `<h2 class="mb-2 mb-lg-4" editable="inline">Related Articles</h2><div class="live-shortcode" lc-helper="posts-loop">[lc_get_posts post_type="post" posts_per_page="4" tax_query="category=related" output_view="lc_get_posts_card_view" output_number_of_columns="2" output_no_results_message="No other posts in this category" output_article_class="h-100 " output_hide_elements="Author, Date, Excerpt, Category, Comments" output_excerpt_length="15" output_heading_tag="h5" output_link_class="link-dark text-decoration-none" output_featured_image_format="medium" output_featured_image_class="w-100" ]</div>`
			},
		],
	},
	"WooCommerce Templating not tested": {
		description: "WooCommerce templating fragments for product and shop views.",
		blocks: [
			{
				name: "Product Title",
				icon_html: `<i aria-hidden="true" class="fa fa-header"></i>`,
				template_html: `<h2>[lc_wc_product get="name"]</h2>`
			},
			{
				name: "Product Price",
				icon_html: `<i aria-hidden="true" class="fa fa-usd"></i>`,
				template_html: `[lc_wc_product get="price_html"]`
			},
			{
				name: "Product Short Description",
				icon_html: `<i aria-hidden="true" class="fa fa-file-text-o"></i>`,
				template_html: `[lc_wc_product get="short_description" filter="wpautop"]`
			},
			{
				name: "Product Description",
				icon_html: `<i aria-hidden="true" class="fa fa-file-text"></i>`,
				template_html: `[lc_wc_product get="description" filter="wpautop"]`
			},
			{
				name: "Product Category",
				icon_html: `<i aria-hidden="true" class="fa fa-folder-open-o"></i>`,
				template_html: `[lc_wc_product_category]`
			},
			{
				name: "Product Category URL",
				icon_html: `<i aria-hidden="true" class="fa fa-folder-open-o" style="position: relative;"><i aria-hidden="true" class="fa fa-link" style="position: absolute; top: -26px; left: 11px; font-size: 11px;"></i></i>`,
				template_html: `[lc_wc_product_category get="url"]`
			},
			{
				name: "Product Categories",
				icon_html: `<i aria-hidden="true" class="fa fa-folder-o" style="position: relative;"><i class="fa fa-folder-o" style="opacity:0.5;position:absolute;top: -6px; left: -6px;"></i></i>`,
				template_html: `[lc_the_terms taxonomy="product_cat" class="badge rounded-pill bg-info" link="1" parent=""]`
			},
			{
				name: "On Sale Badge",
				icon_html: `<i aria-hidden="true" class="fa fa-certificate"></i>`,
				template_html: `[lc_wc_on_sale_badge class="badge bg-success rounded-pill"]`
			},
			{
				name: "Product Rating",
				icon_html: `<i aria-hidden="true" class="fa fa-star"></i>`,
				template_html: `[lc_wc_rating]`
			},
			{
				name: "Product Rating Template",
				icon_html: `<i aria-hidden="true" class="fa fa-star" style="position: relative;"><span style="font-size: 10px;font-weight: 900;position: absolute; left: 20px; "> ...... </span></i>`,
				template_html: `<div class="d-flex"> [lc_wc_product_rating] </div>`
			},
			{
				name: "Product ID",
				icon_html: `<span style="font-weight:900;font-family:monospace; ">ID</span>`,
				template_html: `[lc_wc_product get="ID"]`
			},
			{
				name: "Product SKU",
				icon_html: `<span style="font-weight:900;font-family:monospace; ">SKU</span>`,
				template_html: `[lc_wc_product get="sku"]`
			},
			{
				name: "Product Permalink",
				icon_html: `<i aria-hidden="true" class="fa fa-link" style="transform: rotate(135deg);"></i>`,
				template_html: `[lc_wc_product get="permalink"]`
			},
			{
				name: "Product Dimensions",
				icon_html: `<i aria-hidden="true" class="fa fa-cube"></i>`,
				template_html: `[lc_wc_product get="dimensions"]`
			},
			{
				name: "Related Products",
				icon_html: `<i aria-hidden="true" class="fa fa-random"></i>`,
				template_html: `[lc_wc_related columns="4" posts_per_page="4" orderby="rand" order="desc"]`
			},
			{
				name: "Product Carousel",
				icon_html: `<i aria-hidden="true" class="fa fa-picture-o"></i>`,
				template_html: `[lc_wc_carousel]`
			},
			{
				name: "WC Notices",
				icon_html: `<i aria-hidden="true" class="fa fa-exclamation-circle"></i>`,
				template_html: `[lc_wc_notices]`
			},
			{
				name: "Add To Cart Form",
				icon_html: `<i aria-hidden="true" class="fa fa-cart-plus"></i>`,
				template_html: `[lc_wc_product_add_to_cart class='btn-dark btn ' qty_class='' select_class='form-control']`
			},
			{
				name: "Description Tab",
				icon_html: `<i aria-hidden="true" class="fa fa-info-circle"></i>`,
				template_html: `[lc_wc_product_tab_description]`
			},
			{
				name: "Additional Information Tab",
				icon_html: `<i aria-hidden="true" class="fa fa-list-alt"></i>`,
				template_html: `[lc_wc_product_tab_additional_information]`
			},
			{
				name: "Reviews Tab",
				icon_html: `<i aria-hidden="true" class="fa fa-comments"></i>`,
				template_html: `[lc_wc_product_tab_reviews]`
			},
			{
				name: "Loop: Add To Cart",
				icon_html: `<i aria-hidden="true" class="fa fa-shopping-bag"></i>`,
				template_html: `[lc_wc_add_to_cart class="btn btn-outline-primary"]`
			},
			{
				name: "Loop: Order by",
				icon_html: `<i aria-hidden="true" class="fa fa-sort"></i>`,
				template_html: `[lc_wc_order_by]`
			},
			{
				name: "Loop: Number of Results",
				icon_html: `<i aria-hidden="true" class="fa fa-list-ol"></i>`,
				template_html: `[lc_wc_result_count]`
			},
			{
				name: "WooCommerce Filters Sidebar for Shop page only",
				icon_html: `<i aria-hidden="true" class="fa fa-sliders"></i>`,
				template_html: `[lc_wc_sidebar]`
			},
			{
				name: "Product Data",
				icon_html: `<i aria-hidden="true" class="fa fa-database"></i>`,
				template_html: `<div id="lc-helper" lc-helper="shortcode">[lc_wc_product get="id"]</div>`
			},
		],
	},
};
