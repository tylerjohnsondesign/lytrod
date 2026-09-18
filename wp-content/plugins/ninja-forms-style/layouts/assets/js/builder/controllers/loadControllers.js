define( 
	[
		'controllers/data',
		'controllers/maxCols',
		'controllers/addField',
		'controllers/cellSortable',
		'controllers/gutterDroppable',
		'controllers/rowsSortable',
		'controllers/undo',
		'controllers/updateFieldOrder',
		'controllers/stylesVisualEditor'
	], 
	function
	(
		Data,
		MaxCols,
		AddField,
		CellSortable,
		GutterDroppable,
		RowsSortable,
		Undo,
		UpdateFieldOrder,
		StylesVisualEditor
	)
	{
	var controller = Marionette.Object.extend( {
		initialize: function() {
			new MaxCols();
			new Data();
			new AddField();
			new CellSortable();
			new GutterDroppable();
			new RowsSortable();
			new Undo();
			new UpdateFieldOrder();
			new StylesVisualEditor();
		}

	});

	return controller;
} );
