<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:param name="title" select="'MCF Growth Groups'"/>
	<xsl:output method="html" encoding="iso-8859-1" indent="no"/>
 <xsl:template match="items">
 

 <h1><xsl:value-of select="$title"/></h1>
<ul>
  
 <xsl:for-each select="item">
 	<xsl:variable name="email">
 	<xsl:value-of select="owner_email_primary" />
 </xsl:variable>
	 <li><xsl:value-of select="meet_day_name"/>s at <xsl:value-of select="meet_time_name"/> </li>
	 Address: <br />
	Contact <a href="mailto:{$email}"><xsl:value-of select="owner_name"/></a>
</xsl:for-each>
</ul>

 </xsl:template>
</xsl:stylesheet>