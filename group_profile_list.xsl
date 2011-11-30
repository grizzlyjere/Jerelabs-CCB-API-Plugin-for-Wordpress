<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:param name="ccburl" select="''"/>
	<xsl:output method="html" encoding="iso-8859-1" indent="no"/>

<!-- Supress nodes we're not matching -->
<xsl:template match="text()|@*" />

 <xsl:template match="groups">

<ul>
  
 <xsl:for-each select="group[group_type='Care / Small Group' and public_search_listed='true' and listed='true']">
 	<xsl:sort data-type="number" order="ascending"
          select="((meeting_day='Monday') * 1) +
          ((meeting_day='Tuesday') * 2) +
          ((meeting_day='Wednesday') * 3) +
          ((meeting_day='Thursday') * 4) +
          ((meeting_day='Friday') * 5) +
          ((meeting_day='Saturday') * 5) +
          ((meeting_day='Sunday') * 5) "
          />

    <xsl:variable name="group_id">
    		<xsl:value-of select="@id" />
    </xsl:variable>

 	<xsl:variable name="email">
 		<xsl:value-of select="main_leader/email" />
 	</xsl:variable>

 	<xsl:variable name="leader_displayname">
 		<xsl:value-of select="main_leader/first_name"/>
 	</xsl:variable>

 	<xsl:variable name="leader_fullname">
 		<xsl:value-of select="main_leader/full_name"/>
 	</xsl:variable>

 	<xsl:variable name="leader_id">
 		<xsl:value-of select="main_leader/@id"/>
 	</xsl:variable>

 	<xsl:variable name="dow">
 		<xsl:value-of select="meeting_day" />
 	</xsl:variable>

	<xsl:variable name="time">
 		<xsl:value-of select="meeting_time" />
 	</xsl:variable>

 	<xsl:variable name="location">
 		<xsl:value-of select="concat(addresses/address[1]/street_address,', ',addresses/address[1]/city, ', ', addresses/address[1]/state)" />
 	</xsl:variable>
 
	 <li><xsl:value-of select="$dow"/>s (<xsl:value-of select="$time"/>)</li>
	 <ul>
	 	<li> <xsl:value-of select="description" /></li>
		 <li>Address: <xsl:value-of select="$location" /></li>
		 <li>Contact <a href="javascript:void(0)"  onclick="javascript:window.open('{$ccburl}/easy_email.php?ax=create_new&individual_id={$leader_id}&group_id={$group_id}&individual_full_name={$leader_fullname}','Email','scrollbars=1,width=520,height=710');return false;"><xsl:value-of select="$leader_displayname"/></a></li>
	</ul>
</xsl:for-each>
</ul>

 </xsl:template>
</xsl:stylesheet>