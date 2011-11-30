<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:param name="ccburl" select="''"/>
  <xsl:param name="currentDate" select="''" />
  <!-- Assumes 2011-12-07 -->
  <xsl:output method="html" encoding="iso-8859-1" indent="no"/>

<!-- Supress nodes we're not matching -->
<xsl:template match="text()|@*" />

 <xsl:template match="items">

<ul>
  
 <xsl:for-each select="item">
   <li><xsl:value-of select="event_name"/></li>
   <ul>
    <li>
     <xsl:choose>
        <xsl:when test="date = $currentDate">
          <xsl:value-of select="'Today'" />
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="FormatDate">
            <xsl:with-param name="DateToFormat" select="date" />
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
<xsl:value-of select="' at '" />
      <xsl:call-template name="FormatTime">
        <xsl:with-param name="TimeToFormat" select="start_time" />
      </xsl:call-template>
    </li>
<xsl:if test="string-length(location) > 0">
<li>Location: 
<xsl:value-of select="location" />
</li>
</xsl:if>

  </ul>
</xsl:for-each>
</ul>
</xsl:template>

<!-- Based on http://geekswithblogs.net/workdog/archive/2007/02/08/105858.aspx -->
<xsl:template name="FormatDate">
    <xsl:param name="DateToFormat" />
    <!-- Assumes 2011-12-07 -->
    <xsl:variable name="year">
      <xsl:value-of select="substring($DateToFormat,1,4)" />
    </xsl:variable>
    <xsl:variable name="month">
      <xsl:value-of select="substring($DateToFormat,6,2)" />
    </xsl:variable>
    <xsl:variable name="day">
      <xsl:value-of select="substring($DateToFormat,9,2)" />
    </xsl:variable>

    <xsl:choose>
      <xsl:when test="$month = '01'">January </xsl:when>
      <xsl:when test="$month = '02'">February </xsl:when>
      <xsl:when test="$month = '03'">March </xsl:when>
      <xsl:when test="$month = '04'">April </xsl:when>
      <xsl:when test="$month = '05'">May </xsl:when>
      <xsl:when test="$month = '06'">June </xsl:when>
      <xsl:when test="$month = '07'">July </xsl:when>
      <xsl:when test="$month = '08'">August </xsl:when>
      <xsl:when test="$month = '09'">September </xsl:when>
      <xsl:when test="$month = '10'">October </xsl:when>
      <xsl:when test="$month = '11'">November </xsl:when>
      <xsl:when test="$month = '12'">December </xsl:when>
    </xsl:choose>

    <xsl:value-of select="$day" />
    <xsl:value-of select="', '" />
    <xsl:value-of select="$year" />
  </xsl:template>

<xsl:template name="FormatTime">
    <xsl:param name="TimeToFormat" />
    <!-- Assumes 23:12:10 -->
    <xsl:variable name="hour">
      <xsl:value-of select="substring($TimeToFormat,1,2)" />
    </xsl:variable>
    <xsl:variable name="min-temp">
      <xsl:value-of select="substring($TimeToFormat,4,2)" />
    </xsl:variable>
    <xsl:variable name="min">
      <xsl:choose>
        <xsl:when test="substring($min-temp,1,1) = '0'">
          <xsl:value-of select="substring($min-temp,2,1)" />
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$min-temp" />
        </xsl:otherwise>
    </xsl:choose>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="number($hour) > 12">
            <xsl:value-of select="number($hour)-12" />
            <xsl:value-of select="':'" />
            <xsl:value-of select="$min" />
            <xsl:value-of select="' pm'" />
      </xsl:when>
      <xsl:otherwise>
            <xsl:value-of select="$hour" />
            <xsl:value-of select="':'" />
            <xsl:value-of select="$min" />
            <xsl:value-of select="' am'" />
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>