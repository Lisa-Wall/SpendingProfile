
var Validate =
{
	email:function(sValue)
	{
		return sValue.match(/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/);
	},

	date:function(sValue)
	{
		return CDate.validate(sValue);
	},

	float:function(sValue)
	{
		return !isNaN( parseFloat(sValue) );
	}
}
