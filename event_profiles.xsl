<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:param name="ccburl" select="''"/>
	<xsl:output method="html" encoding="iso-8859-1" indent="no"/>

<!-- Supress nodes we're not matching -->
<xsl:template match="text()|@*" />

 <xsl:template match="groups">

<ul>
  
 <xsl:for-each select="group[group_type='Care / Small Group' and campus='MCF Community Church' and public_search_listed='true' and listed='true']">

    <xsl:variable name="event_id">
    		<xsl:value-of select="@id" />
    </xsl:variable>

 	<xsl:variable name="location">
 		<xsl:value-of select="concat(addresses/address[1]/street_address,', ',addresses/address[1]/city, ', ', addresses/address[1]/state)" />
 	</xsl:variable>
 
	 <li><xsl:value-of select="$name"/></li>
	 <ul>
	 	<li> <xsl:value-of select="description" /></li>
		 <li>Address: <xsl:value-of select="$location" /></li>
	</ul>
</xsl:for-each>
</ul>

 </xsl:template>
</xsl:stylesheet>