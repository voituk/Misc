/** 
* reduce / inject function implementation for collections
*/
$.inject = function(collection, accumulator, iterator, context) {
	$.each(collection, function(index, value){
		accumulator = iterator.call(context, accumulator, index, value)
	})
	return accumulator
}
