<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:param name="ccburl" select="''"/>
  <xsl:param name="group_type" select="'*'"/>
  
	<xsl:output method="html" encoding="iso-8859-1" indent="no"/>


<!-- Supress nodes we're not matching -->
<xsl:template match="text()|@*" />


<xsl:template match="groups">
<table width="100%" id="ccbgroups">
  <thead>
  <tr>
    <th>Type</th>
    <th>Name</th>
    <th>Day</th>
    <th>Time</th>
    <th>Leader</th>
    <th>Location</th>
  </tr>  

  <tr id="ccbgroupsfilter">
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
</thead>
<tbody>
    <xsl:apply-templates select="group[group_type=$group_type or $group_type='*']" />
  </tbody>

</table>

</xsl:template>

<xsl:template match="group">
    <xsl:variable name="group_id">
        <xsl:value-of select="@id" />
    </xsl:variable>
    <xsl:variable name="leader_id">
      <xsl:value-of select="main_leader/@id"/>
    </xsl:variable>
      <xsl:variable name="leader_displayname">
    <xsl:value-of select="main_leader/first_name"/>
  </xsl:variable>

  <xsl:variable name="leader_fullname">
    <xsl:value-of select="main_leader/full_name"/>
  </xsl:variable>

	 	<tr>
      <td><xsl:value-of select="group_type" /></td>
      <td><xsl:value-of select="name" /></td>
      <td><xsl:value-of select="meeting_day" /></td>
      <td><xsl:value-of select="meeting_time" /></td>
      <td><a href="javascript:void(0)"  onclick="javascript:window.open('{$ccburl}/easy_email.php?ax=create_new&individual_id={$leader_id}&group_id={$group_id}&individual_full_name={$leader_fullname}','Email','scrollbars=1,width=520,height=710');return false;"><xsl:value-of select="$leader_displayname"/></a></td>
      <td><xsl:apply-templates select="addresses/address[1]" /></td>
    </tr>
 </xsl:template>

<xsl:template match="addresses/address[1]">
        <xsl:value-of select="city"/>
        <xsl:if test="string-length(city) and string-length(state)">
            <xsl:text> ,</xsl:text>
        </xsl:if>
        <xsl:value-of select="state"/>
</xsl:template>


</xsl:stylesheet>